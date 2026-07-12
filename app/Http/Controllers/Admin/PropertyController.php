<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\IndexPropertyRequest;
use App\Http\Requests\Property\StorePropertyRequest;
use App\Http\Requests\Property\UpdatePropertyRequest;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Property\Application\Actions\ArchivePropertyAction;
use Domain\Property\Application\Actions\CreatePropertyAction;
use Domain\Property\Application\Actions\UpdatePropertyAction;
use Domain\Property\Application\Commands\ArchivePropertyCommand;
use Domain\Property\Application\Commands\CreatePropertyCommand;
use Domain\Property\Application\Commands\UpdatePropertyCommand;
use Domain\Property\Enums\PropertyStatus;
use Domain\Property\Enums\PropertyType;
use Domain\Property\Services\PropertyQueryService;
use Domain\Property\Services\PropertyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertyController extends Controller
{
    public function __construct(private CurrentOrganization $organization, private AuthorizationService $authorization) {}

    public function index(IndexPropertyRequest $request, PropertyQueryService $queries): View
    {
        $membership = $this->membership($request);

        return view('admin.properties.index', ['properties' => $queries->paginate($membership, $request->validated())->withQueryString(), 'filters' => $request->validated(), 'abilities' => $this->abilities($membership)]);
    }

    public function create(Request $request): View
    {
        $membership = $this->membership($request);
        abort_unless($this->authorization->can($membership, 'property.properties.create'), 403);

        return view('admin.properties.create', $this->formData());
    }

    public function store(StorePropertyRequest $request, CreatePropertyAction $action): RedirectResponse
    {
        $p = $action->execute(new CreatePropertyCommand($this->membership($request), $request->validated()));

        return redirect()->route('admin.properties.edit', $p)->with('status', 'Đã tạo cơ sở lưu trú.');
    }

    public function edit(Request $request, string $property, PropertyService $properties): View
    {
        $membership = $this->membership($request);
        abort_unless($this->authorization->can($membership, 'property.properties.update'), 403);

        return view('admin.properties.edit', array_merge($this->formData(), ['property' => $properties->find($property)]));
    }

    public function update(UpdatePropertyRequest $request, string $property, PropertyService $properties, UpdatePropertyAction $action): RedirectResponse
    {
        $p = $action->execute(new UpdatePropertyCommand($this->membership($request), $properties->find($property), $request->validated()));

        return redirect()->route('admin.properties.edit', $p)->with('status', 'Đã cập nhật cơ sở lưu trú.');
    }

    public function destroy(Request $request, string $property, PropertyService $properties, ArchivePropertyAction $action): RedirectResponse
    {
        $action->execute(new ArchivePropertyCommand($this->membership($request), $properties->find($property)));

        return redirect()->route('admin.properties.index')->with('status', 'Đã lưu trữ cơ sở lưu trú.');
    }

    private function membership(Request $request): OrganizationUser
    {
        return OrganizationUser::query()->where('user_id', $request->user()->getAuthIdentifier())->where('organization_id', $this->organization->id())->firstOrFail();
    }

    private function abilities(OrganizationUser $m): array
    {
        return ['create' => $this->authorization->can($m, 'property.properties.create'), 'update' => $this->authorization->can($m, 'property.properties.update'), 'archive' => $this->authorization->can($m, 'property.properties.archive')];
    }

    private function formData(): array
    {
        return ['types' => PropertyType::cases(), 'statuses' => PropertyStatus::cases()];
    }
}
