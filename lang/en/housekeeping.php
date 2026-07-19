<?php

return [
    'messages' => [
        'task_assigned' => 'Housekeeping task assigned successfully.',
        'task_started' => 'Housekeeping task started successfully.',
        'task_completed' => 'Housekeeping task completed successfully.',
        'task_cancelled' => 'Housekeeping task cancelled successfully.',
        'task_created' => 'Housekeeping task created successfully.',
        'task_updated' => 'Housekeeping task updated successfully.',
    ],

    'validation' => [
        'organization_required' => 'Current organization context is required.',
        'task_invalid_organization' => 'Housekeeping task does not belong to the current organization.',
        'unit_invalid_organization' => 'Unit does not belong to the current organization.',
        'assignee_required' => 'Assignee is required.',
        'task_completed' => 'Completed housekeeping tasks cannot be assigned.',
        'task_in_progress' => 'Housekeeping task already in progress.',
        'task_start_invalid' => 'Only assigned housekeeping tasks can be started.',
        'task_complete_invalid' => 'Only in-progress housekeeping tasks can be completed.',
    ],

    'attributes' => [
        'assignee_id' => 'assignee',
        'task' => 'task',
    ],
];