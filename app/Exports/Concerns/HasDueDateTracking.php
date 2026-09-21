<?php

namespace App\Exports\Concerns;

use App\Models\ProductionOrder;

/**
 * Columnas de seguimiento de fecha límite ("Estado de tiempo" / "Días
 * hábiles restantes") compartidas entre reportes — delega a los accessors
 * de ProductionOrder (time_tracking_label / business_days_remaining), que
 * son la única fuente de este criterio (también usados en las tarjetas de
 * "Mis tareas"). No dupliques esta lógica aquí.
 */
trait HasDueDateTracking
{
    /**
     * [Estado de tiempo, Días hábiles restantes].
     */
    protected function dueDateTrackingColumns(ProductionOrder $order): array
    {
        return [$order->time_tracking_label, $order->business_days_remaining];
    }
}
