<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Shared behaviour for the External read-only API.
 *
 * Every endpoint is GET only. Nothing in this namespace writes to the database,
 * which is the structural guarantee that a consumer cannot modify atlinehelp.
 */
abstract class BaseApiController extends Controller
{
    protected const MAX_PER_PAGE = 200;
    protected const DEF_PER_PAGE = 100;

    /**
     * Apply incremental-sync filtering.
     *
     * `?updated_since=2026-08-01T00:00:00Z` returns only rows touched since
     * then. Without it a consumer would have to pull the whole table every run.
     *
     * Soft-deleted rows are INCLUDED when filtering incrementally, so the
     * consumer can tombstone its mirror. Without this, a record deleted in
     * atlinehelp would live forever downstream.
     */
    protected function applySince(Builder $query, Request $request, string $table): Builder
    {
        $since = $request->query('updated_since');
        if (!$since) {
            return $query;
        }

        try {
            $ts = new \DateTimeImmutable($since);
        } catch (\Exception) {
            return $query;
        }

        $query->withTrashed();

        return $query->where(function ($q) use ($ts, $table) {
            $q->where("{$table}.updated_at", '>=', $ts)
              ->orWhere("{$table}.deleted_at", '>=', $ts);
        });
    }

    protected function perPage(Request $request): int
    {
        $n = (int) $request->query('per_page', self::DEF_PER_PAGE);
        if ($n < 1) {
            $n = self::DEF_PER_PAGE;
        }
        return min($n, self::MAX_PER_PAGE);
    }

    /**
     * Uniform envelope so the consumer can page without guessing.
     *
     * @param  \Illuminate\Contracts\Pagination\LengthAwarePaginator  $page
     */
    protected function paginated($page, callable $transform): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => array_map($transform, $page->items()),
            'meta'    => [
                'current_page' => $page->currentPage(),
                'per_page'     => $page->perPage(),
                'total'        => $page->total(),
                'last_page'    => $page->lastPage(),
                'has_more'     => $page->hasMorePages(),
            ],
            'server_time' => now()->toIso8601String(),
        ]);
    }

    protected function item(array $data): JsonResponse
    {
        return response()->json([
            'success'     => true,
            'data'        => $data,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    protected function notFound(string $message = 'Not found.'): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], 404);
    }

    /** ISO-8601 or null — never a raw Carbon dump. */
    protected function iso($value): ?string
    {
        if (!$value) {
            return null;
        }
        return $value instanceof \DateTimeInterface
            ? $value->format(\DateTimeInterface::ATOM)
            : (string) $value;
    }

    /** Date only (Y-m-d) or null. */
    protected function date($value): ?string
    {
        if (!$value) {
            return null;
        }
        return $value instanceof \DateTimeInterface
            ? $value->format('Y-m-d')
            : substr((string) $value, 0, 10);
    }
}
