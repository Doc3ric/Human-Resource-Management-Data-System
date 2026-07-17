<x-dashboard-app>
    <style>
        .details-card {
            background: var(--color-surface, #ffffff);
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            border: 1px solid var(--color-border, #e2e8f0);
            padding: 32px;
            max-width: 800px;
            margin: 0 auto;
        }

        .details-header {
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--color-border, #e2e8f0);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .details-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }

        .detail-item {
            margin-bottom: 16px;
        }

        .detail-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .detail-value {
            font-size: 1.1rem;
            color: #334155;
            font-weight: 500;
        }

        .status-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-actions {
            display: flex;
            gap: 12px;
            padding-top: 24px;
            border-top: 1px solid var(--color-border, #e2e8f0);
        }

        .btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-back:hover {
            background: #e2e8f0;
            color: #334155;
        }

        .btn-edit {
            background: #0f4c75;
            color: #ffffff;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-edit:hover {
            background: #1a5276;
            color: #ffffff;
        }
    </style>

    <div class="px-4 py-5">
        <div class="details-card">
            <div class="details-header">
                <h2 class="details-title">
                    <i class="bi bi-building text-primary"></i> Office Details
                </h2>
                <span class="status-badge {{ $organizationalUnit->status === 'Active' ? 'status-active' : 'status-inactive' }}">
                    {{ $organizationalUnit->status }}
                </span>
            </div>

            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">Office Name</div>
                    <div class="detail-value">{{ $organizationalUnit->name }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Sub-Office</div>
                    <div class="detail-value">{{ $organizationalUnit->sub_office ?? 'None specified' }}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Office Code</div>
                    <div class="detail-value">{{ $organizationalUnit->code }}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Created At</div>
                    <div class="detail-value">{{ $organizationalUnit->created_at ? $organizationalUnit->created_at->format('M d, Y h:i A') : 'N/A' }}</div>
                </div>
            </div>

            <div class="btn-actions">
                <a href="{{ route('organizational-units.index') }}" class="btn-back">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
                <a href="{{ route('organizational-units.edit', $organizationalUnit->id) }}" class="btn-edit">
                    <i class="bi bi-pencil"></i> Edit Office
                </a>
            </div>
        </div>
    </div>
</x-dashboard-app>
