<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Checkout Form -->
    @permission('internal_inventory_checkout.create')
    <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; border: 1px solid #e5e7eb;">
        <h3 style="font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 16px 0;">
            <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle; margin-right: 4px;">output</span>
            Checkout Asset
        </h3>
        <form action="{{ route('internal.inventory.checkout') }}" method="POST">
            @csrf
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Asset *</label>
                    <select name="internal_asset_id" required
                            style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px; min-height: 32px;">
                        <option value="">Select Available Asset</option>
                        @foreach($availableAssets as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->asset_tag }} - {{ $asset->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Employee *</label>
                    <select name="employee_id" required
                            style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px; min-height: 32px;">
                        <option value="">Select Employee</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->position ?? 'No Position' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Expected Return Date *</label>
                    <input type="date" name="expected_return_date" required min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                           style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px; min-height: 32px;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Purpose *</label>
                    <input type="text" name="purpose" required placeholder="e.g., Client presentation, Site visit"
                           style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px; min-height: 32px;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Notes</label>
                    <textarea name="notes" rows="2" placeholder="Additional notes..."
                              style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px;"></textarea>
                </div>
                <button type="submit"
                        style="margin-top: 8px; padding: 10px 16px; background-color: #2563eb; color: white; border: none; border-radius: 4px; font-size: 11px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                    <span class="material-symbols-outlined" style="font-size: 14px;">output</span>
                    CHECKOUT
                </button>
            </div>
        </form>
    </div>
    @endpermission

    <!-- Currently Checked Out -->
    <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; border: 1px solid #e5e7eb;">
        <h3 style="font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 16px 0;">
            <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle; margin-right: 4px;">input</span>
            Currently Checked Out ({{ $checkedOutAssets->count() }})
        </h3>
        
        @if($checkedOutAssets->isEmpty())
            <p style="font-size: 11px; color: #9ca3af; text-align: center; padding: 40px 0;">No assets currently checked out</p>
        @else
            <div style="max-height: 400px; overflow-y: auto;">
                @foreach($checkedOutAssets as $movement)
                @php
                    $isOverdue = $movement->expected_return_date < now()->startOfDay();
                @endphp
                <div style="padding: 12px; margin-bottom: 8px; background-color: white; border-radius: 6px; border: 1px solid {{ $isOverdue ? '#fecaca' : '#e5e7eb' }};">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                        <div>
                            <span style="font-size: 12px; font-weight: 600; color: #2563eb;">{{ $movement->asset->asset_tag }}</span>
                            <span style="font-size: 11px; color: #6b7280; margin-left: 8px;">{{ $movement->asset->name }}</span>
                        </div>
                        @if($isOverdue)
                            <span style="font-size: 10px; padding: 2px 8px; background-color: #fef2f2; color: #dc2626; border-radius: 9999px; font-weight: 500;">OVERDUE</span>
                        @endif
                    </div>
                    <div style="font-size: 11px; color: #4b5563; margin-bottom: 4px;">
                        <strong>Employee:</strong> {{ $movement->employee->full_name ?? '-' }}
                    </div>
                    <div style="font-size: 11px; color: #4b5563; margin-bottom: 4px;">
                        <strong>Purpose:</strong> {{ $movement->purpose }}
                    </div>
                    <div style="font-size: 11px; color: {{ $isOverdue ? '#dc2626' : '#4b5563' }}; margin-bottom: 8px;">
                        <strong>Due:</strong> {{ $movement->expected_return_date->format('d/m/Y') }}
                        @if($isOverdue)
                            ({{ $movement->expected_return_date->diffInDays(now()) }} days overdue)
                        @endif
                    </div>
                    <button type="button" onclick="openCheckinModal({{ $movement->id }}, '{{ $movement->asset->asset_tag }}', '{{ $movement->asset->name }}')"
                            style="padding: 6px 12px; background-color: #10b981; color: white; border: none; border-radius: 4px; font-size: 10px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                        <span class="material-symbols-outlined" style="font-size: 12px;">input</span>
                        CHECK IN
                    </button>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Checkin Modal -->
<div id="checkinModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 9999;">
    <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: white; border-radius: 8px; width: 400px; z-index: 10000;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e5e7eb;">
            <h3 style="font-size: 14px; font-weight: 600; color: #111827;">Check In Asset</h3>
            <p id="checkinAssetInfo" style="font-size: 11px; color: #6b7280; margin-top: 4px;"></p>
        </div>
        <form id="checkinForm" method="POST">
            @csrf
            <div style="padding: 20px;">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Return Condition *</label>
                    <select name="return_condition" required
                            style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px; min-height: 32px;">
                        <option value="excellent">Excellent</option>
                        <option value="good" selected>Good</option>
                        <option value="fair">Fair</option>
                        <option value="poor">Poor</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Notes</label>
                    <textarea name="notes" rows="2" placeholder="Any issues or comments..."
                              style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px;"></textarea>
                </div>
            </div>
            <div style="padding: 16px 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" onclick="closeCheckinModal()"
                        style="padding: 8px 16px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px; background: white; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit"
                        style="padding: 8px 16px; background-color: #10b981; color: white; border: none; border-radius: 4px; font-size: 11px; cursor: pointer;">
                    Check In
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openCheckinModal(movementId, assetTag, assetName) {
    document.getElementById('checkinAssetInfo').textContent = assetTag + ' - ' + assetName;
    document.getElementById('checkinForm').action = '/internal/inventory/checkin/' + movementId;
    document.getElementById('checkinModal').style.display = 'block';
}

function closeCheckinModal() {
    document.getElementById('checkinModal').style.display = 'none';
}

document.getElementById('checkinModal').addEventListener('click', function(e) {
    if (e.target === this) closeCheckinModal();
});
</script>
@endpush
