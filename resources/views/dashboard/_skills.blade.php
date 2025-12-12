<h5 class="fw-bold mt-5 mb-3">
    Įgūdžiai tobulėjimui
</h5>

<div class="row g-4 mb-4">

    @if(isset($skills) && $skills->count())
        @foreach($skills as $skill)
            <div class="col-md-6">
                <div class="dashboard-card h-100">
                    <div class="card-body">
                        <h6 class="fw-bold mb-1">
                            {{ $skill->title }}
                        </h6>

                        <p class="small text-muted mb-2">
                            {{ $skill->description }}
                        </p>

                        <div class="progress mb-1" style="height: 5px;">
                            <div class="progress-bar bg-success"
                                 style="width: {{ $skill->progress }}%;">
                            </div>
                        </div>

                        <small class="text-muted">
                            Progresas: {{ $skill->progress }}%
                        </small>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        {{-- Empty state --}}
        <div class="col-12">
            <div class="dashboard-card p-4 text-center text-muted">
                Dar nepridėjai jokių įgūdžių 🌱
            </div>
        </div>
    @endif

</div>
