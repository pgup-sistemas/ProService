<?php
/**
 * proService - ImportJob Model
 * Arquivo: /app/models/ImportJob.php
 */

namespace App\Models;

class ImportJob extends Model
{
    protected string $table = 'import_jobs';

    /**
     * Retorna jobs pendentes para a empresa atual
     *
     * @param int $limit
     * @return array
     */
    public function findPending(int $limit = 10): array
    {
        return $this->findAll(['status' => 'pending'], 'created_at ASC', $limit);
    }

    /**
     * Busca job por ID (mesmo comportamento do Model::findById com filtro por empresa)
     */
    public function findJobById(int $id): ?array
    {
        return $this->findById($id);
    }
}
