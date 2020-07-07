<?php

namespace Artificerkal\LaravelPendingChange\Traits;

use Artificerkal\LaravelPendingChange\PendingChange;

trait HasPendingChange
{

    /**
     * Is set to true to stop relations from being serialized
     * 
     * @var bool
     */
    protected $hideRelations = false;

    /**
     * Retrieve the PendingChange associated with this model
     *
     * @return null|Artificerkal\LaravelPendingChange\PendingChange|\Illuminate\Database\Eloquent\Relations\MorphOne
     */
    protected function pendingChange()
    {
        return $this->morphOne(PendingChange::class, 'updateable');
    }

    /**
     * Save the changes to this model for a future update
     *
     * @param string|array $options
     * @return $this
     */
    public function pendingSave($options = [])
    {
        $options = is_string($options) ? func_get_args() : $options;

        $this->syncChanges();

        $data = $this->getChanges();

        if (\in_array('append', $options) && $this->pendingChange && is_array($this->pendingChange->data))
            $data = \array_merge($this->pendingChange->data, $data);

        \optional($this->pendingChange()->updateOrCreate([], ['data' => $data]), function ($pendingChange) {
            $this->pendingChange = $pendingChange;
        });

        if (\in_array('reset', $options)) {
            \tap($this, function ($model) {
                foreach ($model->getOriginal() as $key => $value) {
                    $this->setAttribute($key, $value);
                }
            })->syncChanges();
        }

        return $this;
    }

    /**
     * Set this model to be deleted at a future point
     *
     * @return $this
     */
    public function pendingDelete()
    {
        $this->syncChanges();

        $this->pendingChange()->updateOrCreate([], ['data' => 'delete']);

        return $this;
    }

    /**
     * Apply the pending changes to this model
     *
     * @return $this
     */
    public function applyPending($options = [])
    {
        $options = is_string($options) ? func_get_args() : $options;

        \optional($this->pendingChange, function ($pendingChange) use (&$options) {
            if ($pendingChange->data == 'delete')
                $this->delete();
            else {
                foreach ($pendingChange->data as $key => $value) {
                    $this->setAttribute($key, $value);
                }
                $this->save();
            }

            $this->removePending();
        });

        return $this;
    }

    /**
     * Remove all pending changes
     *
     * @return $this
     */
    public function removePending()
    {
        \optional($this->pendingChange, function ($pendingChange) {
            $pendingChange->delete();
            unset($this->pendingChange);
        });

        return $this;
    }


    public function getDraftDataAttribute()
    {
        $this->makeHidden('pendingChange');

        if (!$this->pendingChange) return null;

        if ($this->pendingChange->data == 'delete') return $this->pendingChange->data;

        $attributes = \array_merge($this->getAttributes(), $this->pendingChange->data);

        $draftVersion = $this->replicate();
        unset($draftVersion->pendingChange);

        foreach ($attributes as $key => $value) {
            $draftVersion->setAttribute($key, $value);
        }

        $draftVersion->hideRelations = true;

        return $draftVersion;
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        if ($this->hideRelations) return $this->attributesToArray();
        return parent::toArray();
    }
}
