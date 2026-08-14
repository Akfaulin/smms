<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddScheduledPublishToContentPlan extends Migration
{
    public function up()
    {
        $fields = [
            'jam_publish' => [
                'type'       => 'TIME',
                'null'       => true,
                'after'      => 'tanggal_publish',
            ],
            'scheduled_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'after'      => 'jam_publish',
            ],
            'is_scheduled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'scheduled_at',
            ],
            'publish_attempt' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'after'      => 'is_scheduled',
            ],
            'last_publish_error' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'publish_attempt',
            ],
            'is_processing' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'last_publish_error',
            ],
        ];

        $this->forge->addColumn('content_plan', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('content_plan', [
            'jam_publish',
            'scheduled_at',
            'is_scheduled',
            'publish_attempt',
            'last_publish_error',
            'is_processing',
        ]);
    }
}
