<?php

return [
    'messages' => [
        'task_assigned' => 'Đã phân công công việc buồng phòng thành công.',
        'task_started' => 'Đã bắt đầu công việc buồng phòng.',
        'task_completed' => 'Đã hoàn thành công việc buồng phòng.',
        'task_cancelled' => 'Đã hủy công việc buồng phòng.',
        'task_created' => 'Đã tạo công việc buồng phòng.',
        'task_updated' => 'Đã cập nhật công việc buồng phòng.',
    ],

    'validation' => [
        'organization_required' => 'Chưa xác định tổ chức hiện tại.',
        'task_invalid_organization' => 'Công việc buồng phòng không thuộc tổ chức hiện tại.',
        'unit_invalid_organization' => 'Phòng không thuộc tổ chức hiện tại.',
        'assignee_required' => 'Vui lòng chọn nhân viên phụ trách.',
        'task_completed' => 'Không thể phân công công việc đã hoàn thành.',
        'task_in_progress' => 'Công việc đang được thực hiện.',
        'task_start_invalid' => 'Chỉ công việc đã phân công mới có thể bắt đầu.',
        'task_complete_invalid' => 'Chỉ công việc đang thực hiện mới có thể hoàn thành.',
    ],

    'attributes' => [
        'assignee_id' => 'nhân viên phụ trách',
        'task' => 'công việc',
    ],
];