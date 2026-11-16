<x-layout title="For painters" description="Claim your free FreshCoat listing and get painting jobs from homeowners near you.">
    <div class="wrap stack-lg">
        <div class="split">
            <div class="stack">
                <span class="tape">For painters</span>
                <h1>More jobs nearby. No fee until you finish one.</h1>
                <p class="muted" style="max-width: 52ch">Your business may already be listed. Claim it to set your service area, prices and photos, and start getting requests from homeowners near you.</p>
                <div class="row">
                    <a class="btn btn-primary" href="{{ route('claim.index') }}">Claim my business</a>
                    <a class="btn" href="{{ route('claim.create') }}">My business isn't listed</a>
                </div>
            </div>
            <div class="card stack-sm">
                <span class="eyebrow">What it costs</span>
                <p><strong class="num">$0/mo</strong> to list and get requests.</p>
                <p><strong class="num">2.9% + 30¢</strong> Stripe processing on jobs you complete.</p>
                <p><strong class="num">6 months free</strong> of the Pro toolkit, then $7.95/mo if you want it.</p>
            </div>
        </div>
        <section class="stack">
            <h2>Included free</h2>
            <div class="features">
                <div class="feat"><h3>Requests by distance</h3><p class="muted">Set a radius in miles. Homeowners nearest you reach you first.</p></div>
                <div class="feat"><h3>Small invite lists</h3><p class="muted">Each request goes to at most 8 painters, so your quote gets seen.</p></div>
                <div class="feat"><h3>Paid on completion</h3><p class="muted">The customer's card is on file before you start. No chasing invoices.</p></div>
                <div class="feat"><h3>No lead fees</h3><p class="muted">Quote as many jobs as you want. You only pay processing on jobs you finish.</p></div>
            </div>
        </section>
        <section class="stack">
            <h2>Pro toolkit</h2>
            <div class="features">
                <div class="feat"><h3>Square-foot estimator</h3><p class="muted">Walls, ceilings and trim by linear foot, coats and sheen, priced from your rates.</p></div>
                <div class="feat"><h3>Paint &amp; materials</h3><p class="muted">Gallons per job, colors by room and supplier receipts in one place.</p></div>
                <div class="feat"><h3>Crews &amp; scheduling</h3><p class="muted">Assign crews and track hours for payroll.</p></div>
                <div class="feat"><h3>Contracts &amp; invoices</h3><p class="muted">E-sign estimates, deposits, payment links and year-end tax reports.</p></div>
            </div>
            <p class="muted small">The Pro toolkit is on the roadmap and not part of this first release.</p>
        </section>
    </div>
</x-layout>
