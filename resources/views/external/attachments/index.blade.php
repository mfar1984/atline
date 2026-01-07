@extends('layouts.app')

@section('title', 'Attachments')

@section('page-title', 'Attachments')

@section('content')
<div class="bg-white border border-gray-200">
    <div class="px-6 py-4 flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Attachment List</h2>
            <p class="text-xs text-gray-500 mt-0.5">View and manage all uploaded attachments</p>
        </div>
    </div>

    <div class="px-6 py-3">
        <form id="filter-form" action="{{ route('external.attachments.index') }}" method="GET" class="flex items-center gap-2">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search file name..." 
                       class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                       style="font-family: Poppins, sans-serif; min-height: 32px;">
            </div>
            <select name="project_id" class="px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 min-w-[140px]" style="font-family: Poppins, sans-serif; min-height: 32px;">
                <option value="">All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                @endforeach
            </select>
            <select name="file_type" class="px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 min-w-[120px]" style="font-family: Poppins, sans-serif; min-height: 32px;">
                <option value="">All Types</option>
                <option value="image" {{ request('file_type') == 'image' ? 'selected' : '' }}>Images</option>
                <option value="document" {{ request('file_type') == 'document' ? 'selected' : '' }}>Documents</option>
                <option value="spreadsheet" {{ request('file_type') == 'spreadsheet' ? 'selected' : '' }}>Spreadsheets</option>
            </select>
            <button type="submit" class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">search</span>
                SEARCH
            </button>
            <button type="button" onclick="window.location.href='{{ route('external.attachments.index') }}'" class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition" style="min-height: 32px; background-color: #dc2626;">
                <span class="material-symbols-outlined" style="font-size: 14px;">refresh</span>
                RESET
            </button>
        </form>
    </div>

    @if(session('success'))
    <div class="px-6 pb-3">
        <div class="px-4 py-3 bg-green-50 border border-green-200 rounded">
            <p class="text-xs text-green-800" style="font-family: Poppins, sans-serif;">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="px-6 pb-3">
        <div class="px-4 py-3 bg-red-50 border border-red-200 rounded">
            <p class="text-xs text-red-800" style="font-family: Poppins, sans-serif;">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <div class="px-6">
        <x-ui.data-table
            :headers="[
                ['label' => 'File Name', 'align' => 'text-left'],
                ['label' => 'Type', 'align' => 'text-center'],
                ['label' => 'Storage', 'align' => 'text-center'],
                ['label' => 'Size', 'align' => 'text-right'],
                ['label' => 'Uploaded By', 'align' => 'text-center'],
                ['label' => 'Date', 'align' => 'text-center'],
                ['label' => 'Actions', 'align' => 'text-center']
            ]"
            :actions="false"
            empty-message="No attachments found."
        >
            @forelse($attachments as $attachment)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                        @php
                            $iconMap = [
                                'image/jpeg' => 'image',
                                'image/png' => 'image',
                                'image/jpg' => 'image',
                                'image/gif' => 'image',
                                'application/pdf' => 'picture_as_pdf',
                                'application/msword' => 'description',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'description',
                                'application/vnd.ms-excel' => 'table_chart',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'table_chart',
                            ];
                            $icon = $iconMap[$attachment->file_type] ?? 'attach_file';
                            $iconColor = $attachment->isImage() ? 'text-green-600' : ($attachment->isDocument() ? 'text-red-600' : 'text-blue-600');
                        @endphp
                        <span class="material-symbols-outlined {{ $iconColor }}" style="font-size: 20px;">{{ $icon }}</span>
                        <div>
                            <span class="text-xs font-medium text-gray-900 block" style="font-family: Poppins, sans-serif;">{{ $attachment->file_name }}</span>
                            @if($attachment->attachable)
                                <span class="text-xs text-gray-500">
                                    @if($attachment->attachable_type === 'App\\Models\\Project')
                                        <span class="inline-flex px-1.5 py-0.5 text-xs rounded bg-blue-100 text-blue-800" style="font-size: 9px;">Project</span>
                                        {{ $attachment->attachable->name ?? '-' }}
                                    @elseif($attachment->attachable_type === 'App\\Models\\Asset')
                                        <span class="inline-flex px-1.5 py-0.5 text-xs rounded bg-purple-100 text-purple-800" style="font-size: 9px;">Asset</span>
                                        {{ $attachment->attachable->asset_tag ?? '-' }}
                                    @endif
                                </span>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    @php
                        $typeLabel = 'Other';
                        $typeColor = 'bg-gray-100 text-gray-800';
                        if ($attachment->isImage()) {
                            $typeLabel = 'Image';
                            $typeColor = 'bg-green-100 text-green-800';
                        } elseif ($attachment->isDocument()) {
                            $typeLabel = 'Document';
                            $typeColor = 'bg-red-100 text-red-800';
                        } elseif (str_contains($attachment->file_type, 'spreadsheet') || str_contains($attachment->file_type, 'excel')) {
                            $typeLabel = 'Spreadsheet';
                            $typeColor = 'bg-emerald-100 text-emerald-800';
                        }
                    @endphp
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $typeColor }}">
                        {{ $typeLabel }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    @if($attachment->isR2Storage())
                        <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-sky-100 text-sky-800">
                            <span class="material-symbols-outlined" style="font-size: 12px;">cloud</span>
                            Cloud
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                            <span class="material-symbols-outlined" style="font-size: 12px;">folder</span>
                            Local
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                    <span class="text-xs text-gray-600">{{ $attachment->formatted_size }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="text-xs text-gray-600">{{ $attachment->uploader->name ?? '-' }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="text-xs text-gray-500">@formatDate($attachment->created_at)</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                    <x-ui.action-buttons
                        :show-url="route('external.attachments.download', $attachment)"
                        :delete-onclick="auth()->user()->hasPermission('external_attachments.delete') ? 'deleteAttachment(' . $attachment->id . ')' : null"
                    />
                </td>
            </tr>
            @empty
            @endforelse
        </x-ui.data-table>
    </div>

    <div class="px-6 py-3">
        <x-ui.custom-pagination :paginator="$attachments" record-label="attachments" />
    </div>
</div>

@push('scripts')
<script>
function deleteAttachment(id) {
    window.showDeleteModal('{{ route("external.attachments.index") }}/' + id);
}
</script>
@endpush
@endsection
