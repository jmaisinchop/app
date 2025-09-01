<?php
function getAllowedDepartments(array $departments, array $dptsUser, object $dataUser): array
{
    $excludedIds = [];

    foreach ($dptsUser as $dptUsr) {
        $dptUsr = (int)$dptUsr;

        // 1. Si el usuario pertenece a Departamentos Comerciales (IDs 3, 5 o 6)
        if (in_array($dptUsr, [3, 5, 6])) {
            // Excluir el departamento "Crédito-Desembolso"
            $excludedIds[] = getParamNumber('CREDIT_DISBURSEMENT');
            break;
        }

        // 2. Si el usuario pertenece a "Administración de crédito"
        if ($dptUsr === getParamNumber('CREDIT_DISBURSEMENT_ENABLED')) {
            // Excluir el departamento "Atención al Cliente"
            $excludedIds[] = getParamNumber('ATTENTION_CLIENT');
            break;
        }

        // 3. Para cualquier otro tipo de departamento:
        // Excluir tanto "Atención al Cliente" como "Crédito-Desembolso"
        if ($dataUser->email === 'icandanedo@austrobank.com') {
            continue;
        } else {
            $excludedIds[] = getParamNumber('ATTENTION_CLIENT');
            $excludedIds[] = getParamNumber('CREDIT_DISBURSEMENT');
            break;
        }
    }

    return array_filter($departments, function ($item) use ($excludedIds) {
        return $item->id_padre == 0 && !in_array((int)$item->id, $excludedIds);
    });
}
