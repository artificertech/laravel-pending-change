<?php

namespace Artificerkal\LaravelPendingChange\Traits;

use Artificerkal\LaravelPendingChange\PendingChange;

trait HasPendingChange
{

    /**
     * Retrieve the PendingChange associated with this model
     *
     * @return null|Artificerkal\LaravelPendingChange\PendingChange|\Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function pendingChange()
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

        $this->pendingChange()->updateOrCreate([], ['data' => $data]);

        if (\in_array('reset', $options)) {
            $this->fill($this->getOriginal())->syncChanges();
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
    public function applyPending()
    {
        \optional($this->pendingChange, function ($pendingChange) {
            if ($pendingChange->data == 'delete')
                $this->delete();
            else
                $this->fill($pendingChange->data)->save();

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
        $this->pendingChange->refresh()->delete();

        return $this;
    }
}
