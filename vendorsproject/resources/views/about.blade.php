@extends('layouts.main')
@section('title', 'About Us — Nakawa Market')
@section('content')
<section class="about-hero">
    <div class="about-hero-copy">
        <span class="eyebrow">About the project</span>
        <h1>A more connected <em>Nakawa Market.</em></h1>
        <p>Nakawa Market Online is a student research project proposing an easier way for local vendors and shoppers to find one another.</p>
        <a class="btn btn-primary" href="{{ route('marketplace') }}">Explore the marketplace</a>
    </div>
    <aside class="about-hero-note">
        <span class="about-note-mark">N</span>
        <span class="eyebrow">Our idea</span>
        <strong>Bring Nakawa's vendors and shoppers together in one online place.</strong>
        <small>Local trade, made easier to discover.</small>
    </aside>
</section>

<section class="about-story">
    <div class="about-story-heading">
        <span class="eyebrow">Why we made it</span>
        <h2>Research inspired by local trade</h2>
    </div>
    <div class="about-story-copy">
        <p>This project was created as research work by a student group at MUBS. It explores how an online marketplace could link vendors in Nakawa Market with people interested in shopping there.</p>
        <p>The proposal is to make local goods and vendors easier to find, help shoppers browse from one place, and support trade within the Nakawa community.</p>
    </div>
</section>

<section class="about-goals">
    <div class="about-section-heading">
        <span class="eyebrow">Our proposed goals</span>
        <h2>What Nakawa Market Online aims to do</h2>
        <p>These are the project's intended outcomes as it explores a digital link between vendors and shoppers.</p>
    </div>
    <div class="about-objectives">
        <article class="about-objective">
            <span>01</span>
            <h3>Give vendors more visibility</h3>
            <p>Provide local businesses with a place to present their stalls, goods, and categories to online shoppers.</p>
        </article>
        <article class="about-objective">
            <span>02</span>
            <h3>Make local goods easier to find</h3>
            <p>Help shoppers discover produce, household goods, clothing, and textiles offered by Nakawa vendors.</p>
        </article>
        <article class="about-objective">
            <span>03</span>
            <h3>Simplify browsing and ordering</h3>
            <p>Bring vendor product listings together so customers can browse, add items to a cart, and place an order.</p>
        </article>
        <article class="about-objective">
            <span>04</span>
            <h3>Support trade in Nakawa</h3>
            <p>Explore how a simple digital connection can encourage convenient shopping while keeping trade connected to local vendors.</p>
        </article>
    </div>
</section>

<section class="about-team">
    <div class="about-team-mark">C</div>
    <div>
        <span class="eyebrow">The founding team</span>
        <h2>Climax</h2>
        <p>A group of BBC students at MUBS who developed this project as research work.</p>
    </div>
</section>

<aside class="about-prototype-note">
    <strong>A note about the project</strong>
    <p>Nakawa Market Online is a proposed marketplace presented as a prototype. Payments are recorded manually, and delivery or collection arrangements are made directly with vendors.</p>
</aside>

<section class="about-cta">
    <div><span class="eyebrow">Discover Nakawa</span><h2>Ready to explore the market?</h2></div>
    <a class="btn btn-primary" href="{{ route('marketplace') }}">Browse local goods</a>
</section>
@endsection
