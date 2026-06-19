<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait HandlesNestedForms
{
    /**
     * Save nested child records
     * 
     * @param string $childModel - Model class name for child
     * @param array $childData - Array of child records data
     * @param string $parentKey - Foreign key field name in child table
     * @param mixed $parentId - Parent record ID
     * @param bool $deleteExisting - Whether to delete existing child records first
     * @return array - Array of created child IDs
     */
    protected function saveNestedChildren($childModel, $childData, $parentKey, $parentId, $deleteExisting = false)
    {
        if ($deleteExisting) {
            $childModel::where($parentKey, $parentId)->delete();
        }

        $createdIds = [];
        
        if (!empty($childData) && is_array($childData)) {
            foreach ($childData as $child) {
                if (!empty($child)) {
                    $child[$parentKey] = $parentId;
                    $childRecord = $childModel::create($child);
                    $createdIds[] = $childRecord->id;
                }
            }
        }

        return $createdIds;
    }

    /**
     * Update nested child records
     * Handles create, update, and delete operations
     * 
     * @param string $childModel - Model class name for child
     * @param array $childData - Array of child records with 'id' for updates
     * @param string $parentKey - Foreign key field name in child table
     * @param mixed $parentId - Parent record ID
     * @return array - Result with created, updated, and deleted counts
     */
    protected function updateNestedChildren($childModel, $childData, $parentKey, $parentId)
    {
        $result = [
            'created' => 0,
            'updated' => 0,
            'deleted' => 0,
        ];

        if (empty($childData) || !is_array($childData)) {
            // Delete all existing if no data provided
            $result['deleted'] = $childModel::where($parentKey, $parentId)->delete();
            return $result;
        }

        // Get existing child IDs
        $existingIds = $childModel::where($parentKey, $parentId)
            ->pluck('id')
            ->toArray();

        $submittedIds = [];

        foreach ($childData as $child) {
            if (empty($child)) {
                continue;
            }

            $child[$parentKey] = $parentId;

            if (isset($child['id']) && !empty($child['id'])) {
                // Update existing
                $childModel::where('id', $child['id'])
                    ->where($parentKey, $parentId)
                    ->update($child);
                $submittedIds[] = $child['id'];
                $result['updated']++;
            } else {
                // Create new
                unset($child['id']); // Remove id if present
                $childRecord = $childModel::create($child);
                $submittedIds[] = $childRecord->id;
                $result['created']++;
            }
        }

        // Delete removed records
        $idsToDelete = array_diff($existingIds, $submittedIds);
        if (!empty($idsToDelete)) {
            $result['deleted'] = $childModel::whereIn('id', $idsToDelete)->delete();
        }

        return $result;
    }

    /**
     * Extract nested form data from request
     * 
     * @param Request $request
     * @param string $key - Key name in request (e.g., 'personilposko')
     * @return array
     */
    protected function extractNestedData(Request $request, $key)
    {
        $data = $request->input($key, []);
        
        // Handle single record or array of records
        if (!is_array($data)) {
            return [];
        }

        // If first element is not an array, wrap it
        if (!empty($data) && !is_array(reset($data))) {
            return [$data];
        }

        return $data;
    }
}
