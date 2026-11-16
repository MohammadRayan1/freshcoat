@php use App\Support\Fees; @endphp
<x-layout title="Pricing" description="Free to list. Homeowners pay a 5% service fee capped at $6.95. Painters pay card processing only.">
    <div class="wrap stack-lg">
        <div class="stack">
            <span class="tape">Pricing</span>
            <h1>$0 to list. Card processing only, per job.</h1>
            <p class="muted" style="max-width: 60ch">No monthly fee, no charge per lead, no charge until a job is finished.</p>
        </div>
        <div class="price-grid">
            <div class="plan">
                <span class="eyebrow">Homeowners</span>
                <div class="big">5%</div>
                <p class="muted">Service fee added to the job price, <strong>capped at {{ Fees::money(Fees::SERVICE_FEE_CAP_CENTS) }}</strong>.</p>
                <ul><li>Free to request quotes</li><li>Card charged after the job is done</li><li>Cancel before completion and pay nothing</li></ul>
            </div>
            <div class="plan feature">
                <span class="eyebrow">Painters · Free listing</span>
                <div class="big">$0<span class="muted" style="font-size: 1rem">/mo</span></div>
                <p class="muted">Stripe card processing of <strong>2.9% + 30¢</strong> per job, paid from your payout.</p>
                <ul><li>Show up in search by distance</li><li>Unlimited quote requests</li><li>Payouts to your bank through Stripe</li></ul>
                <a class="btn btn-primary" href="{{ route('claim.index') }}">Claim your business</a>
            </div>
            <div class="plan">
                <span class="eyebrow">Painters · Pro toolkit</span>
                <div class="big">{{ Fees::money(Fees::PRO_MONTHLY_CENTS) }}<span class="muted" style="font-size: 1rem">/mo</span></div>
                <p class="muted"><strong>Free for {{ Fees::PRO_TRIAL_MONTHS }} months</strong> when you claim your business. No card required.</p>
                <ul><li>Square-foot estimates and e-sign contracts</li><li>Crew scheduling</li><li>Paint and materials tracking</li><li>Invoices and tax reports</li></ul>
            </div>
        </div>

        <section class="stack">
            <h2>What a typical job costs</h2>
            <div class="table-scroll">
                <table class="ledger">
                    <thead><tr><th>Job price</th><th class="amt">Service fee</th><th class="amt">Homeowner pays</th><th class="amt">Card processing (est.)</th><th class="amt">Painter receives</th></tr></thead>
                    <tbody>
                        @foreach([45000, 120000, 420000] as $cents)
                            @php $total = Fees::customerTotal($cents); $processing = Fees::processing($total); @endphp
                            <tr>
                                <td class="num">{{ Fees::money($cents) }}</td>
                                <td class="amt">{{ Fees::money(Fees::serviceFee($cents), true) }}</td>
                                <td class="amt">{{ Fees::money($total, true) }}</td>
                                <td class="amt">−{{ Fees::money($processing, true) }}</td>
                                <td class="amt">{{ Fees::money($total - $processing - Fees::serviceFee($cents), true) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="muted small">Stripe charges card processing on the full amount the homeowner pays. Rates shown are Stripe's standard US card rates; some cards cost more.</p>
        </section>

        <div class="notice">
            <strong>Customer pays you directly</strong>
            <span class="muted">You can let a customer pay you by check or cash instead. No card processing applies, and the same service fee is billed to you when the booking is confirmed.</span>
        </div>
    </div>
</x-layout>
