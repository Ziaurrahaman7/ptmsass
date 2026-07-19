{{-- Recursive strategy-map node. Expects $goal (with ->childGoals attached in the controller). --}}
<div class="goal-node">
    @include('company.goals.partials.goal-card', ['goal' => $goal])

    @if($goal->childGoals->isNotEmpty())
    <div class="goal-node-children">
        @foreach($goal->childGoals as $child)
            @include('company.goals.partials.goal-node', ['goal' => $child])
        @endforeach
    </div>
    @endif
</div>
