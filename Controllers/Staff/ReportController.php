<?php
/**
 * @package AboHelpdesk
 * @author: Adrian Carchipulla
 * @Copyright (c) 2022
 * @link https://www.linkedin.com/in/adrian-carchipulla/
 */

namespace App\Controllers\Staff;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use App\Controllers\BaseController;
use Config\Services;

class ReportController extends BaseController
{

    public function form()
    {
        $departments = $this->staff->getData('department');
        $hasAccess = (is_array($departments) && in_array(14, $departments)) || $this->staff->getData('admin') == 1;

        if (!$hasAccess) {
            return redirect()->route('staff_dashboard');
        }

        return view('staff/report_form');
    }

    public function generateExcel()
    {
        if ($this->request->getPost('do') !== 'submit') {
            return redirect()->back();
        }

        $validation = Services::validation();
        $validation->setRule('fechaInicio', 'fechaInicio', 'required', [
            'required' => lang('Ingrese la fecha de inicio.')
        ]);
        $validation->setRule('fechaFin', 'fechaFin', 'required', [
            'required' => lang('Ingrese la fecha final.')
        ]);

        if ($validation->withRequest($this->request)->run() == false) {
            $error_msg = $validation->listErrors();

            return view('staff/report_form', [
                'error_msg' => isset($error_msg) ? $error_msg : null,
                'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null
            ]);
        }

        //Se obtiene fechas desde el formulariio
        $startDateForm = $this->request->getPost('fechaInicio');
        $endDateForm = $this->request->getPost('fechaFin');

        // Validar formato y rango de fechas
        $resultado = $this->validarYRangoFechas($startDateForm, $endDateForm);
        if (!$resultado['success']) {
            return view('staff/report_form', [
                'error_msg' => $resultado['message'],
                'success_msg' => $this->session->getFlashdata('form_success')
            ]);
        }

        $startDate = $resultado['start']->format('Y-m-d');
        $endDate = $resultado['end']->format('Y-m-d');
        $departmentId = 14;

        $reportLibrary = new \App\Libraries\Reports();
        $spreadsheet = new Spreadsheet();

        // Hoja 1: Reporte General
        $ticketsGeneral = $reportLibrary->getReportGeneralAttentionClientByDateRange($departmentId, $startDate, $endDate);
        $this->generateSheet(
            $spreadsheet,
            'Reporte General',
            $this->getGeneralHeaders(),
            $ticketsGeneral,
            $reportLibrary,
            true
        );

        // Hoja 2: Reporte Detalle
        $ticketsDetail = $reportLibrary->getReportDetailAttentionClientByDateRange($departmentId, $startDate, $endDate);
        $this->generateSheet(
            $spreadsheet,
            'Reporte Detalle',
            $this->getDetailHeaders(),
            $ticketsDetail,
            $reportLibrary,
            false
        );

        // Descargar
        $writer = new Xlsx($spreadsheet);
        $filename = 'reporte_cliente_' . date('Ymd_His') . '.xlsx';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', "attachment; filename=\"$filename\"")
            ->setHeader('Cache-Control', 'max-age=0')
            ->setBody($this->streamExcel($writer));
    }

    private function streamExcel(Xlsx $writer): string
    {
        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }

    private function generateSheet($spreadsheet, $title, $headers, $tickets, $reportLibrary, $isGeneral)
    {
        $sheet = ($spreadsheet->getSheetCount() === 1 && $title === 'Reporte General')
            ? $spreadsheet->getActiveSheet()
            : $spreadsheet->createSheet();

        $sheet->setTitle($title);

        // Escribir encabezados
        foreach ($headers as $i => $header) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . '1', $header);
        }

        // Escribir contenido
        $row = 2;
        foreach ($tickets as $ticket) {
            $typeSolicitudeIds = unserialize($ticket->type_solicitude);
            if (!is_array($typeSolicitudeIds)) {
                $typeSolicitudeIds = [];
            }

            $values = $isGeneral
                ? $this->buildGeneralRow($ticket, $typeSolicitudeIds, $reportLibrary)
                : $this->buildDetailRow($ticket, $typeSolicitudeIds, $reportLibrary);

            foreach ($values as $i => $value) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
                $sheet->setCellValue($col . $row, $value);
            }

            $row++;
        }

        $this->formatSheet($sheet, 1);
    }

    private function getGeneralHeaders(): array
    {
        $headersFirst = [
            'TICKET',
            'USUARIO ENVIA',
            'FECHA INICIO',
            'ULTIMA FECHA DE ACTUALIZACIÓN',
            'TIEMPO',
        ];

        $headersLast = [
            'ESTADO',
        ];

        // Obtener los nombres dinámicos desde getNamesTypeSolicitude
        $reportLibrary = new \App\Libraries\Reports();
        $dynamicHeaders = array_map(function ($item) {
            return $item->description;
        }, $reportLibrary->getNamesTypeSolicitude());

        return array_merge($headersFirst, $dynamicHeaders, $headersLast);
    }

    private function getDetailHeaders(): array
    {
        $headersFirst = [
            'TICKET',
            'USUARIO ENVIA',
            'ULTIMA FECHA DE ACTUALIZACIÓN',
        ];

        $headersLast = [
            'ESTADO',
            'ESTADO FINAL'
        ];

        // Obtener los nombres dinámicos desde getNamesTypeSolicitude
        $reportLibrary = new \App\Libraries\Reports();
        $dynamicHeaders = array_map(function ($item) {
            return $item->description;
        }, $reportLibrary->getNamesTypeSolicitude());

        return array_merge($headersFirst, $dynamicHeaders, $headersLast);
    }

    private function buildGeneralRow($ticket, $typeSolicitudeIds, $reportLibrary): array
    {
        return array_merge([
            $ticket->ticket_id,
            $ticket->fullname_user,
            $ticket->date,
            $ticket->last_date,
            $ticket->time
        ], $this->buildTypeSolicitudeFlags($typeSolicitudeIds, $reportLibrary), [
            $ticket->status
        ]);
    }

    private function buildDetailRow($ticket, $typeSolicitudeIds, $reportLibrary): array
    {
        return array_merge([
            $ticket->ticket_id,
            $ticket->fullname_user,
            $ticket->date
        ], $this->buildTypeSolicitudeFlags($typeSolicitudeIds, $reportLibrary), [
            $ticket->status,
            $ticket->status_id === 5 ? "No" : "Si"
        ]);
    }

    private function buildTypeSolicitudeFlags($ids, $reportLibrary): array
    {
        $reportLibrary = new \App\Libraries\Reports();
        $typesSolicitudes = $reportLibrary->getNamesTypeSolicitude();

        $flags = [];
        foreach ($typesSolicitudes as $solicitude) {
            $flags[] = $this->compareIdsTypeSolicitudeWithId($ids, $solicitude) ? '1' : '0';
        }
        return $flags;
    }

    private function formatSheet($sheet, $headerRow = 1)
    {
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();

        // Encabezados en negrita con fondo
        $sheet->getStyle("A{$headerRow}:{$highestColumn}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFDDEEFF']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);

        // Bordes para toda la tabla
        $sheet->getStyle("A{$headerRow}:{$highestColumn}{$highestRow}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Auto tamaño de columnas
        foreach (range('A', $highestColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * Compara dos listas de IDs y verifica si al menos uno coincide.
     *
     * @param array $arrayTypeSolicitud Array de strings (posiblemente separados por coma)
     * @param array $arrayTypeAttentionClient Array de IDs (enteros o strings)
     * @return bool Verdadero si hay coincidencia de al menos un ID
     */
    private function compareIdsTypeSolicitude(array $arrayTypeSolicitud, array $arrayTypeAttentionClient): bool
    {
        $valores = [];

        foreach ($arrayTypeSolicitud as $elemento) {
            // Explota cada string por coma, trim, y convierte en enteros
            $ids = array_map('trim', explode(',', $elemento));
            $valores = array_merge($valores, $ids);
        }

        return count(array_intersect($valores, $arrayTypeAttentionClient)) > 0;
    }

    /**
     * Compara una lista de IDs (posiblemente separados por coma) con un solo ID.
     *
     * @param array $arrayTypeSolicitud Array de strings (posiblemente separados por coma)
     * @param int $idTypeAttentionClient ID individual a comparar
     * @return bool Verdadero si hay coincidencia del ID con alguno de los valores
     */
    private function compareIdsTypeSolicitudeWithId(array $arrayTypeSolicitud, object $solicitude): bool
    {
        $valores = [];

        foreach ($arrayTypeSolicitud as $elemento) {
            $elemento = trim($elemento);

            $ids = [];
            //Caso especial: si empieza con "5,", considerar solo el 5
            if (str_starts_with($elemento, '5,')) {
                $ids = [5];
            }
//                if ($solicitude->type === 'select') { // Para campos SELECT solo se considera el ID
//                $id = (string)$solicitude->id;
//                if (str_starts_with($elemento, '5,')) {
//                    $ids = [(int)$solicitude->id];
//                    //$ids = [5];
//                }
//            }
            elseif ($solicitude->type === 'text') { //Para campos tipo TEXT se valida que no sean vacios
                $id = (string)$solicitude->id;

                // Solo considerar si empieza con "id,"
                if (str_starts_with($elemento, $id . ',')) {
                    // Procesar normalmente
                    $ids = array_map(fn($v) => (int)trim($v), explode(',', $elemento));
                } else {
                    continue; // Omitir si no empieza con "id,"
                }
            } else {
                $ids = array_map(fn($v) => (int)trim($v), explode(',', $elemento));
            }

            $valores = array_merge($valores, $ids);
        }

        return in_array((int)$solicitude->id, $valores, true);
    }


    function validarYRangoFechas(string $startDate, string $endDate): array
    {
        $pattern = '/^\d{2}\/\d{2}\/\d{4}$/';

        if (!preg_match($pattern, $startDate)) {
            return ['success' => false, 'message' => 'La fecha de inicio no tiene el formato dd/mm/yyyy.'];
        }

        if (!preg_match($pattern, $endDate)) {
            return ['success' => false, 'message' => 'La fecha final no tiene el formato dd/mm/yyyy.'];
        }

        $start = \DateTime::createFromFormat('d/m/Y', $startDate);
        $end = \DateTime::createFromFormat('d/m/Y', $endDate);

        if (!$start || !$end) {
            return ['success' => false, 'message' => 'Una o ambas fechas son inválidas.'];
        }

        if ($start > $end) {
            return ['success' => false, 'message' => 'La fecha de inicio no puede ser mayor que la fecha final.'];
        }

        return ['success' => true, 'start' => $start, 'end' => $end];
    }

}