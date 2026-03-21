<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CourseMatchingService
{
    /**
     * Get courses matched to a user's AI analysis results.
     *
     * Priority:
     *  1. Exact field match (primary recommended field)
     *  2. Secondary recommended fields
     *  3. Skill keyword matches across all courses
     *
     * @param array|null $aiAnalysis  The user's ai_analysis JSON column
     * @param int        $limit       Maximum courses to return
     * @return Collection
     */
    public function getMatchedCourses(?array $aiAnalysis, int $limit = 6): Collection
    {
        if (!$aiAnalysis) {
            return collect();
        }

        // 1. Determine the primary recommended field
        $primaryField = $aiAnalysis['recommended_field'] ?? null;

        // 2. Gather all recommended fields (the AI returns an array of [{field, confidence, role}])
        $allFields = [];
        if (!empty($aiAnalysis['recommended_fields']) && is_array($aiAnalysis['recommended_fields'])) {
            foreach ($aiAnalysis['recommended_fields'] as $rf) {
                $field = is_array($rf) ? ($rf['field'] ?? null) : $rf;
                if ($field) {
                    $allFields[] = $field;
                }
            }
        }

        // Make sure primaryField is first
        if ($primaryField && !in_array($primaryField, $allFields)) {
            array_unshift($allFields, $primaryField);
        }

        // 3. Extract user skills for keyword matching
        $skills = $aiAnalysis['skills'] ?? [];

        $matched = collect();
        $seenIds = [];

        // --- Phase 1: Courses from primary field ---
        if ($primaryField) {
            $primaryCourses = Course::forField($primaryField)
                ->inRandomOrder()
                ->limit($limit)
                ->get();

            foreach ($primaryCourses as $course) {
                if (!in_array($course->id, $seenIds)) {
                    $course->match_reason = 'Matches your recommended field';
                    $matched->push($course);
                    $seenIds[] = $course->id;
                }
            }
        }

        // --- Phase 2: Courses from secondary fields ---
        if ($matched->count() < $limit) {
            $secondaryFields = array_slice($allFields, 1); // Skip primary
            foreach ($secondaryFields as $field) {
                if ($matched->count() >= $limit) break;

                $secondaryCourses = Course::forField($field)
                    ->whereNotIn('id', $seenIds)
                    ->inRandomOrder()
                    ->limit($limit - $matched->count())
                    ->get();

                foreach ($secondaryCourses as $course) {
                    if (!in_array($course->id, $seenIds)) {
                        $course->match_reason = "Related field: {$field}";
                        $matched->push($course);
                        $seenIds[] = $course->id;
                    }
                }
            }
        }

        // --- Phase 3: Keyword matches from skills ---
        if ($matched->count() < $limit && !empty($skills)) {
            $topSkills = array_slice($skills, 0, 10);
            $skillCourses = Course::matchingKeywords($topSkills)
                ->whereNotIn('id', $seenIds)
                ->inRandomOrder()
                ->limit($limit - $matched->count())
                ->get();

            foreach ($skillCourses as $course) {
                if (!in_array($course->id, $seenIds)) {
                    $course->match_reason = 'Matches your skills';
                    $matched->push($course);
                    $seenIds[] = $course->id;
                }
            }
        }

        Log::info('CourseMatchingService matched courses', [
            'primary_field' => $primaryField,
            'total_matched' => $matched->count(),
        ]);

        return $matched->take($limit);
    }
}
