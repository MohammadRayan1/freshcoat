<x-layout title="My requests">
    <div class="wrap stack">
        <div class="row between"><h1 style="font-size: 2.2rem">My requests</h1><a class="btn btn-primary" href="{{ route('home') }}">New request</a></div>
        @forelse($quoteRequests as $request)
            <a class="card row between" href="{{ route('quote-requests.show', $request) }}" style="color: inherit; text-decoration: none">
                <div class="stack-sm" style="gap: .2rem">
                    <h3>{{ $request->jobName() }} · {{ $request->city->label() }}</h3>
                    <span class="meta">{{ $request->size_label }} · {{ $request->timingLabel() }} · {{ $request->created_at->diffForHumans() }}</span>
                </div>
                <div class="row">
                    <span class="pill">{{ $request->quotes_count }} {{ str('quote')->plural($request->quotes_count) }}</span>
                    <span class="pill {{ $request->status->value === 'booked' ? 'pill-ok' : 'pill-muted' }}">{{ ucfirst($request->status->value) }}</span>
                </div>
            </a>
        @empty
            <div class="notice"><strong>No requests yet</strong><span class="muted">Tell us what needs painting and nearby painters will send quotes. <a href="{{ route('home') }}">Start a request</a></span></div>
        @endforelse
    </div>
</x-layout>
