<?php

namespace App\Models;

use CodeIgniter\Model;

class AiGenerationLogModel extends Model
{
    protected $table            = 'ai_generation_log';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'content_id',
        'user_id',
        'fitur',
        'prompt_input',
        'output',
        'created_at',
    ];

    // Dates
    protected $useTimestamps = false; // kita urus manual di save atau set custom
}
