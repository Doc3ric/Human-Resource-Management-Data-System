<x-dashboard-app>
    <style>
        .search-hero {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border-radius: 16px;
            padding: 32px;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(30, 41, 59, 0.5);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .search-hero-icon {
            font-size: 48px;
            color: rgba(255,255,255,0.2);
            position: absolute;
            right: 30px;
            top: 50%;
            transform: translateY(-50%);
        }

        .search-input-wrapper {
            position: relative;
            max-width: 600px;
            width: 100%;
        }

        .search-input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 18px;
        }

        .search-input-wrapper input {
            width: 100%;
            padding: 16px 16px 16px 48px;
            border-radius: 99px;
            border: 2px solid transparent;
            background: rgba(255,255,255,0.1);
            color: white;
            font-size: 16px;
            transition: all 0.2s;
        }
        
        .search-input-wrapper input:focus {
            background: rgba(255,255,255,0.15);
            border-color: rgba(255,255,255,0.3);
            outline: none;
            box-shadow: 0 0 0 4px rgba(255,255,255,0.05);
        }

        .search-input-wrapper input::placeholder {
            color: rgba(255,255,255,0.5);
        }

        .search-input-wrapper button {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 99px;
            padding: 8px 20px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .search-input-wrapper button:hover {
            background: #2563eb;
        }

        .result-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            border: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
        }

        .result-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
            border-color: #e2e8f0;
            text-decoration: none;
            color: inherit;
        }

        .result-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #e0e7ff;
            color: #4338ca;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
        }

        .result-info {
            flex: 1;
        }

        .result-name {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 2px;
        }

        .result-details {
            font-size: 13px;
            color: #64748b;
        }

        .result-type {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 4px 10px;
            border-radius: 99px;
        }

        .type-plantilla { background: #dbeafe; color: #1e40af; }
        .type-casual { background: #fce7f3; color: #be185d; }
        .type-job-order { background: #fef3c7; color: #b45309; }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
            border: 1px dashed #cbd5e1;
            color: #94a3b8;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            color: #cbd5e1;
        }

        .empty-state h3 {
            font-size: 18px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 8px;
        }
    </style>

    <div class="search-hero">
        <i class="bi bi-search search-hero-icon"></i>
        <div style="flex: 1; position: relative; z-index: 10;">
            <h1 style="font-size: 24px; font-weight: 800; margin: 0 0 16px 0;">Global Employee Search</h1>
            <form action="{{ route('search') }}" method="GET" class="search-input-wrapper">
                <i class="bi bi-search"></i>
                <input type="text" name="q" value="{{ $query ?? '' }}" placeholder="Search by name, item number..." autocomplete="off" autofocus>
                <button type="submit">Search</button>
            </form>
        </div>
    </div>

    @if(isset($query) && $query !== '')
        <div style="margin-bottom: 20px; font-size: 14px; color: #64748b; font-weight: 500;">
            Found {{ $results->count() }} result(s) for "<span style="color: #0f172a; font-weight: 700;">{{ $query }}</span>"
        </div>

        @if($results->count() > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 16px;">
                @foreach($results as $result)
                    <a href="{{ $result['url'] }}" class="result-card">
                        <div class="result-avatar">
                            @if(!empty($result['profile_picture']))
                                <img src="{{ Storage::url($result['profile_picture']) }}" alt="Profile" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                            @else
                                {{ $result['avatar'] }}
                            @endif
                        </div>
                        <div class="result-info">
                            <div class="result-name">{{ $result['name'] }}</div>
                            <div class="result-details">{{ $result['details'] }}</div>
                        </div>
                        <div>
                            @php
                                $typeClass = match($result['type']) {
                                    'Plantilla' => 'type-plantilla',
                                    'Casual' => 'type-casual',
                                    'Job Order' => 'type-job-order',
                                    default => 'type-plantilla'
                                };
                            @endphp
                            <span class="result-type {{ $typeClass }}">{{ $result['type'] }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-search"></i>
                <h3>No employees found</h3>
                <p>We couldn't find any records matching "{{ $query }}". Please try checking the spelling or use different keywords.</p>
            </div>
        @endif
    @else
        <div class="empty-state">
            <i class="bi bi-keyboard"></i>
            <h3>Start typing to search</h3>
            <p>Search across all employee modules (Permanent, Casual, Job Orders) instantly.</p>
        </div>
    @endif

</x-dashboard-app>
