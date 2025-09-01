<?php
/**
 * @package AboHelpdesk
 * @author: Adrian Carchipulla
 * @Copyright (c) 2025
 * @link https://www.linkedin.com/in/adrian-carchipulla/
 */

 namespace App\Libraries;

 use Config\Database;
 use Config\Services;
 use App\Models\ClientEmailModel;
 use App\Models\ClientSolicitudeModel;
 use App\Models\Tickets;
 use App\Models\Departments;
 use App\Models\TypeSolicitude;

class Reports {

    protected $ticketsModel;
    protected $clientSolicitudModel;
    protected $clientEmailModel;
    protected $settings;
    protected $departmentsModel;
    protected $typeSolicitude;

    public function __construct()
    {
        $this->settings = Services::settings();
        $this->clientEmailModel = new ClientEmailModel();
        $this->ticketsModel = new Tickets();
        $this->clientSolicitudModel = new ClientSolicitudeModel();
        $this->departmentsModel = new Departments();
        $this->typeSolicitude = new TypeSolicitude();

    }

    /**
     * Obtener tickets por departamento y opcionalmente por rango de fechas.
     * Sin paginación (uso en reportes con PhpSpreadsheet)
     */
    public function getReportGeneralAttentionClientByDateRange(
        int $departmentId,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $builder = $this->ticketsModel->builder('tickets t');

        $builder->select([
                't.id AS ticket_id',
                't.user_id AS user_id',
                'u.fullname AS fullname_user',
                'FROM_UNIXTIME(t.date) AS date',
                'FROM_UNIXTIME(t.last_update) AS last_date',
                'SEC_TO_TIME(t.last_update - t.date) AS time',
                "CASE 
                    WHEN t.status = 1 THEN 'Enviado'
                    WHEN t.status = 2 THEN 'Atendido'
                    WHEN t.status = 3 THEN 'Esperando respuesta'
                    WHEN t.status = 4 THEN 'En proceso'
                    WHEN t.status = 5 THEN 'Cerrado'
                    ELSE ' '
                END AS status",
                'hcs.solicitude as type_solicitude'
            ])
            ->join('users as u','u.id=t.user_id')
            ->join('client_solicitude as hcs', 'hcs.ticket = t.id', 'left')
            ->where('t.department_id', $departmentId)
            ->orderBy('t.id');

        if ($startDate && $endDate) {
            // Convertir a timestamp UNIX (inicio del día y fin del día)
            $startTimestamp = strtotime($startDate . ' 00:00:00');
            $endTimestamp   = strtotime($endDate . ' 23:59:59');

            $builder->where('t.date >=', $startTimestamp);
            $builder->where('t.date <=', $endTimestamp);
        }

        return $builder->get()->getResult();
    }

    /**
     * Obtener tickets por departamento y opcionalmente por rango de fechas.
     * Sin paginación (uso en reportes con PhpSpreadsheet)
     */
    public function getReportDetailAttentionClientByDateRange(
        int $departmentId,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $builder = $this->ticketsModel->builder('tickets t');

        $builder->select([
                't.id AS ticket_id',
                'htm.id as message_id',
                't.user_id AS user_id',
                "(CASE 
                    WHEN htm.staff_id = 0 THEN u.fullname 
                    ELSE hs.fullname 
                END) as fullname_user",
                'FROM_UNIXTIME(htm.date) AS date',
                'FROM_UNIXTIME(t.last_update) AS last_date',
                'SEC_TO_TIME(t.last_update - t.date) AS time',
                "CASE 
                    WHEN t.status = 1 THEN 'Enviado'
                    WHEN t.status = 2 THEN 'Atendido'
                    WHEN t.status = 3 THEN 'Esperando respuesta'
                    WHEN t.status = 4 THEN 'En proceso'
                    WHEN t.status = 5 THEN 'Cerrado'
                    ELSE ' '
                END AS status",
                't.status AS status_id',
                'hcs.solicitude as type_solicitude'
            ])
            ->join('users as u','u.id=t.user_id')
            ->join('client_solicitude as hcs', 'hcs.ticket = t.id', 'left')
            ->join('tickets_messages as htm', 'htm.ticket_id = t.id', 'left')
            ->join('staff as hs', 'hs.id = htm.staff_id', 'left')
            ->where('t.department_id', $departmentId)
            ->orderBy('t.id');

        if ($startDate && $endDate) {
            // Convertir a timestamp UNIX (inicio del día y fin del día)
            $startTimestamp = strtotime($startDate . ' 00:00:00');
            $endTimestamp   = strtotime($endDate . ' 23:59:59');

            $builder->where('t.date >=', $startTimestamp);
            $builder->where('t.date <=', $endTimestamp);
        }

        return $builder->get()->getResult();
    }

    /**
     * Mapeo de descripciones informativas por código de categoría.
     */
    private const CATEGORY_LABELS = [
        1 => 'Estados de Cuenta',
        2 => 'Referencia bancaria',
        3 => 'Saldos de cuenta',
        4 => 'Información de cuentas',
        5 => 'Depósito a plazo fijo',
        6 => 'Información de crédito',
        7 => 'Transferencias',
        8 => 'Tarjetas',
        9 => 'Personas',
    ];

    /**
     * Devuelve el nombre descriptivo de una categoría.
     */
    public function getCategoryLabel(int $codeCategory): ?string
    {
        return self::CATEGORY_LABELS[$codeCategory] ?? null;
    }

    /**
     * Obtiene los códigos de tipo de solicitud asociados a una categoría dada.
     *
     * @param int $codeCategory Código de la categoría
     * @return array Lista de códigos de tipo de solicitud asociados
     */
    public function getCodesTypeSolicitude(int $codeCategory): array
    {
        $map = [
            1 => [5],
            2 => [7],
            3 => [5],
            4 => [2, 3, 4, 5, 6, 7, 28, 29, 30],
            5 => [9, 10, 11, 12, 13],
            6 => [15, 16, 17, 18, 19, 31, 40],
            7 => [3],
            8 => [21, 22, 23, 24, 25, 34, 35, 36, 37, 38, 39],
            9 => [27, 33, 32],
        ];

        return $map[$codeCategory] ?? [];
    }

    /**
     * Retorna solo los IDs activos ordenados ascendente.
     */
    public function getArrayTypeSolicitudeByCategory(int $codeCategory): array
    {
        $resultados = $this->typeSolicitude
        ->select('id')
        ->whereIn('id', $this->getCodesTypeSolicitude($codeCategory))
        ->where('enabled',1)
        ->orderBy('solicitude_order','asc')
        ->get()
        ->getResult();

        return array_map(fn($item) => $item->id, $resultados);
    }

    /**
     * Retorna listado de los nombres de tipo de solicitud
     * @return array
     */
    public function getNamesTypeSolicitude(): array
    {
        $q = $this->typeSolicitude
            ->select('id, description, type')
            ->where('enabled', 1)
            ->where('type !=', 'label') // equivalente a type <> 'label'
            ->orderBy('solicitude_order','asc')
            ->get();
        $r = $q->getResult();
        $q->freeResult();;
        return $r;
    }

}