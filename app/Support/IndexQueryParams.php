<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;

final class IndexQueryParams
{
    public const int DEFAULT_PAGE_SIZE = 15;

    /** @var list<int> */
    public const array ALLOWED_PAGE_SIZES = [10, 15, 25, 50, 100];

    /**
     * @param  list<int>  $allowedPageSizes
     * @param  list<string>  $sortableColumns
     */
    public function __construct(
        public readonly int $pageSize,
        public readonly ?string $sortColumn,
        public readonly string $sortDirection,
    ) {}

    /**
     * @param  list<int>  $allowedPageSizes
     * @param  list<string>  $sortableColumns
     */
    public static function fromRequest(
        Request $request,
        int $defaultPageSize = self::DEFAULT_PAGE_SIZE,
        array $allowedPageSizes = self::ALLOWED_PAGE_SIZES,
        array $sortableColumns = [],
        string $defaultSortDirection = 'desc',
    ): self {
        $pageSize = (int) $request->input('per_page', $defaultPageSize);

        if (! in_array($pageSize, $allowedPageSizes, true)) {
            $pageSize = $defaultPageSize;
        }

        $sortColumn = $request->input('sort');
        if (! is_string($sortColumn) || ! in_array($sortColumn, $sortableColumns, true)) {
            $sortColumn = null;
        }

        $sortDirection = strtolower((string) $request->input('direction', $defaultSortDirection));
        if (! in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = $defaultSortDirection;
        }

        return new self(
            pageSize: $pageSize,
            sortColumn: $sortColumn,
            sortDirection: $sortDirection,
        );
    }
}
