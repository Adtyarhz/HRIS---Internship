@php
    $steps = [
        'cv_screening' => 'Screening CV HC',
        'general_knowledge_test' => 'General Knowledge Test HC',
        'user_assessment' => 'Competency Test User Division',
        'hc_interview' => 'Interview HC',
        'bod_interview' => 'Interview Directors',
        'offering_letter' => 'Offering Letter',
    ];

    $currentStage = $stage;
    $canAccessNext = true;
    $processStopped = false;
@endphp

<div class="stepper-container" style="padding: 20px 0; margin-bottom: 40px;">
    <div class="stepper" style="display: flex; justify-content: space-between; position: relative;">
        <div style="position: absolute; top: 35px; left: 5%; right: 5%; height: 3px; background-color: #000; z-index: 0;"></div>

        @foreach ($steps as $key => $label)
            @php
                $progressItem = optional($applicant->recruitmentProgresses)->firstWhere('stage', $key);
                $status = optional($progressItem)->offering_status;

                $bgColor = '#E6E6E6'; // Default abu-abu
                $isClickable = false;
                $isCurrent = $key === $currentStage;

                if ($processStopped) {
                    $isClickable = false;
                } elseif ($status === 'rejected') {
                    $bgColor = '#C73E1D'; // Merah
                    $processStopped = true;
                    $isClickable = false;
                } elseif ($status === 'in_progress') {
                    $bgColor = '#0043CE'; // Biru
                    $canAccessNext = false;
                    $isClickable = false;
                } elseif ($status === 'accepted') {
                    $bgColor = '#1FBF56'; // Hijau
                    $isClickable = true;
                } elseif ($canAccessNext && is_null($status)) {
                    // Hanya satu tahap berikutnya yang bisa diakses
                    $bgColor = '#0043CE';
                    $isClickable = true;
                    $canAccessNext = false;
                }
            @endphp

            <div class="step-wrapper" style="flex: 1; text-align: center; position: relative;">
                @if ($isClickable)
                    <a href="{{ route('recruitment.stage.show', [$applicant->id, $key]) }}"
                       style="width: 70px; height: 70px; border-radius: 50%; display: inline-flex; justify-content: center; align-items: center;
                              background-color: {{ $bgColor }}; color: white; font-weight: bold; font-size: 18px; position: relative; z-index: 1;
                              text-decoration: none;">
                        {{ $loop->iteration }}
                    </a>
                @else
                    <div style="width: 70px; height: 70px; border-radius: 50%; display: inline-flex; justify-content: center; align-items: center;
                                background-color: {{ $bgColor }}; color: white; font-weight: bold; font-size: 18px; position: relative; z-index: 1;">
                        {{ $loop->iteration }}
                    </div>
                @endif

                <div style="margin-top: 10px; font-size: 10px; font-weight: 600; max-width: 100px; margin-left: auto; margin-right: auto; line-height: 1.2;">
                    {!! nl2br(e($label)) !!}
                </div>
            </div>
        @endforeach
    </div>
</div>
