<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Filters\FaqFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFaqRequest;
use App\Http\Requests\UpdateFaqRequest;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use App\Support\IndexQueryParams;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    private const SORTABLE_COLUMNS = [
        'question',
        'order',
        'is_active',
        'created_at',
    ];

    /**
     * Display a listing of FAQs.
     */
    public function index(Request $request, FaqFilter $filter): Response
    {
        $params = IndexQueryParams::fromRequest(
            request: $request,
            sortableColumns: self::SORTABLE_COLUMNS,
        );

        $query = Faq::query()->filter($filter);

        if ($params->sortColumn !== null) {
            $query->orderBy($params->sortColumn, $params->sortDirection);
        } else {
            $query->orderBy('order')->orderBy('id');
        }

        $faqs = $query->paginate($params->pageSize)->withQueryString();

        return Inertia::render('admin/faqs/Index', [
            'faqs' => [
                'data' => FaqResource::collection($faqs->items())->resolve(),
                'meta' => [
                    'current_page' => $faqs->currentPage(),
                    'from' => $faqs->firstItem(),
                    'last_page' => $faqs->lastPage(),
                    'per_page' => $faqs->perPage(),
                    'to' => $faqs->lastItem(),
                    'total' => $faqs->total(),
                ],
                'links' => [
                    'first' => $faqs->url(1),
                    'last' => $faqs->url($faqs->lastPage()),
                    'prev' => $faqs->previousPageUrl(),
                    'next' => $faqs->nextPageUrl(),
                ],
            ],
            'filters' => $request->only([
                'search',
                'is_active',
            ]),
            'sorting' => [
                'column' => $params->sortColumn,
                'direction' => $params->sortDirection,
            ],
            'pagination' => [
                'pageSize' => $params->pageSize,
                'allowedPageSizes' => IndexQueryParams::ALLOWED_PAGE_SIZES,
            ],
        ]);
    }

    /**
     * Store a newly created FAQ in storage.
     */
    public function store(StoreFaqRequest $request): RedirectResponse
    {
        $faq = Faq::create($request->validated());

        return redirect()
            ->route('admin.faqs.index')
            ->with('success', 'FAQ created successfully.');
    }

    /**
     * Update the specified FAQ in storage.
     */
    public function update(UpdateFaqRequest $request, Faq $faq): RedirectResponse
    {
        $faq->update($request->validated());

        return redirect()
            ->route('admin.faqs.index')
            ->with('success', 'FAQ updated successfully.');
    }

    /**
     * Remove the specified FAQ from storage.
     */
    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()
            ->route('admin.faqs.index')
            ->with('success', 'FAQ deleted successfully.');
    }
}
