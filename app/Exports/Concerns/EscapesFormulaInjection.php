<?php

namespace App\Exports\Concerns;

/**
 * Verificado con una prueba real (generar el .xlsx y reabrirlo con
 * PhpSpreadsheet\IOFactory): un valor que empieza con '=' y parsea como
 * fórmula válida se guarda como celda tipo fórmula ejecutable (ver
 * DefaultValueBinder::dataTypeForValue() en phpoffice/phpspreadsheet) —
 * '+', '-' y '@' NO producen ese resultado en este stack (esa checklist es
 * para CSV, no para .xlsx genuino con metadata de tipo de celda). Anteponer
 * un apóstrofe fuerza el tipo de celda a texto plano en vez de fórmula.
 */
trait EscapesFormulaInjection
{
    private function escapeFormula(mixed $value): mixed
    {
        if (is_string($value) && $value !== '' && $value[0] === '=') {
            return "'" . $value;
        }

        return $value;
    }
}
