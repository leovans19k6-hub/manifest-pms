<?php

namespace Domain\Housekeeping\Enums;

enum HousekeepingTaskType: string
{
    case CheckoutCleaning = 'checkout_cleaning';
    case StayoverCleaning = 'stayover_cleaning';
    case DeepCleaning = 'deep_cleaning';
    case Inspection = 'inspection';
    case MaintenanceSupport = 'maintenance_support';
}