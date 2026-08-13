<?php

namespace App\Models;

use CodeIgniter\Model;

class MetaApiLogModel extends Model
{
    protected $table            = 'meta_api_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'endpoint',
        'method',
        'payload',
        'response_code',
        'response_body',
        'status',
        'created_at',
    ];

    protected $useTimestamps = false;
}
