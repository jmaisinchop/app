<?php 
/**
 * @author: Adrian Carchipulla
 * @Copyright (c) 2021, ABOHELPDESK
 * @link https://www.linkedin.com/in/adrian-carchipulla/
 */
namespace App\Models;
use CodeIgniter\Model;

class ClientSolicitudeModel extends Model
{
    protected $table      = 'client_solicitude';
    protected $primaryKey = 'id';

    protected $returnType     = 'object';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
    ];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;
}


