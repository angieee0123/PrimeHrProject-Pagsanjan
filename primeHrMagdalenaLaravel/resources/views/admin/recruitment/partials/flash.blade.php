{{--
    Flash messages and validation errors.

    The error text for a rejected action is the lifecycle rule's own sentence
    ("Only a lapsed position can be reopened…"), carried through verbatim. That
    sentence explains the refusal better than anything reworded here could, and a
    generic "Something went wrong" would make the reader retry a move the rule
    has already refused on the merits.

    Shown above the page's tabs so it is the first thing read after a redirect.
--}}

@if(session('success'))
    <div class="hire-flash is-success" role="status">
        <span aria-hidden="true">✓</span>
        <div>{{ session('success') }}</div>
    </div>
@endif

@if(session('error'))
    <div class="hire-flash is-error" role="alert">
        <span aria-hidden="true">✕</span>
        <div>{{ session('error') }}</div>
    </div>
@endif

@if($errors->any())
    <div class="hire-flash is-error" role="alert">
        <span aria-hidden="true">✕</span>
        <div>
            <strong>That could not be submitted.</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
