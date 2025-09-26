@extends('layouts.app')

@section('title', 'Exam Results - ' . $result->exam->exam_name)

@section('content')
<div class="py-12">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        {{-- Result Status Banner --}}
        @if($result->result_status == 'pass')
            <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-green-800">Congratulations!</h3>
                        <p class="text-sm text-green-700 mt-1">You have successfully passed the {{ $result->exam->exam_name }}.</p>
                    </div>
                </div>
            </div>
        @elseif($result->result_status == 'fail')
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-red-800">Result Status</h3>
                        <p class="text-sm text-red-700 mt-1">Unfortunately, you did not meet the passing criteria for this exam.</p>
                    </div>
                </div>
            </div>
        @elseif($result->result_status == 'withheld')
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-yellow-800">Result Withheld</h3>
                        <p class="text-sm text-yellow-700 mt-1">Your result is currently under review. Please contact the examination office.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Main Result Card --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-6 text-white">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-2xl font-bold">{{ $result->exam->exam_name }}</h1>
                        <p class="mt-1 text-indigo-100">Examination Result</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-indigo-100">Registration Number</p>
                        <p class="text-xl font-semibold">{{ $result->registration->registration_number }}</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                {{-- Score Overview --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-indigo-100 rounded-full mb-3">
                            <span class="text-2xl font-bold text-indigo-600">{{ $result->final_score }}</span>
                        </div>
                        <p class="text-sm text-gray-600">Total Score</p>
                        <p class="text-xs text-gray-500 mt-1">Out of {{ $result->exam->total_marks }}</p>
                    </div>

                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-3">
                            <span class="text-2xl font-bold text-green-600">{{ $result->percentage }}%</span>
                        </div>
                        <p class="text-sm text-gray-600">Percentage</p>
                        <p class="text-xs text-gray-500 mt-1">
                            @if($result->percentage >= 70)
                                Excellent
                            @elseif($result->percentage >= 60)
                                Good
                            @elseif($result->percentage >= 50)
                                Average
                            @else
                                Below Average
                            @endif
                        </p>
                    </div>

                    @if($result->overall_rank)
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-20 h-20 bg-purple-100 rounded-full mb-3">
                                <span class="text-2xl font-bold text-purple-600">#{{ $result->overall_rank }}</span>
                            </div>
                            <p class="text-sm text-gray-600">Overall Rank</p>
                            <p class="text-xs text-gray-500 mt-1">Out of {{ $totalCandidates ?? 'N/A' }}</p>
                        </div>
                    @endif

                    @if($result->percentile)
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-20 h-20 bg-yellow-100 rounded-full mb-3">
                                <span class="text-2xl font-bold text-yellow-600">{{ $result->percentile }}</span>
                            </div>
                            <p class="text-sm text-gray-600">Percentile</p>
                            <p class="text-xs text-gray-500 mt-1">Better than {{ $result->percentile }}%</p>
                        </div>
                    @endif
                </div>

                {{-- Detailed Breakdown --}}
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Detailed Performance</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Question Statistics --}}
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Question Statistics</h4>
                            <dl class="space-y-2">
                                <div class="flex justify-between py-2 border-b">
                                    <dt class="text-sm text-gray-600">Total Questions</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $result->exam->total_questions }}</dd>
                                </div>
                                <div class="flex justify-between py-2 border-b">
                                    <dt class="text-sm text-gray-600">Questions Attempted</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $result->total_questions_attempted }}</dd>
                                </div>
                                <div class="flex justify-between py-2 border-b">
                                    <dt class="text-sm text-gray-600">Correct Answers</dt>
                                    <dd class="text-sm font-medium text-green-600">{{ $result->correct_answers }}</dd>
                                </div>
                                <div class="flex justify-between py-2 border-b">
                                    <dt class="text-sm text-gray-600">Wrong Answers</dt>
                                    <dd class="text-sm font-medium text-red-600">{{ $result->wrong_answers }}</dd>
                                </div>
                                <div class="flex justify-between py-2 border-b">
                                    <dt class="text-sm text-gray-600">Unanswered</dt>
                                    <dd class="text-sm font-medium text-gray-500">{{ $result->unanswered }}</dd>
                                </div>
                                @if($result->negative_marks > 0)
                                    <div class="flex justify-between py-2 border-b">
                                        <dt class="text-sm text-gray-600">Negative Marks</dt>
                                        <dd class="text-sm font-medium text-red-600">-{{ $result->negative_marks }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>

                        {{-- Score Calculation --}}
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Score Calculation</h4>
                            <dl class="space-y-2">
                                <div class="flex justify-between py-2 border-b">
                                    <dt class="text-sm text-gray-600">Marks Obtained</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $result->marks_obtained }}</dd>
                                </div>
                                @if($result->negative_marks > 0)
                                    <div class="flex justify-between py-2 border-b">
                                        <dt class="text-sm text-gray-600">Negative Marking</dt>
                                        <dd class="text-sm font-medium text-red-600">-{{ $result->negative_marks }}</dd>
                                    </div>
                                @endif
                                <div class="flex justify-between py-2 border-b border-gray-300">
                                    <dt class="text-sm font-semibold text-gray-700">Final Score</dt>
                                    <dd class="text-sm font-bold text-gray-900">{{ $result->final_score }}</dd>
                                </div>
                                <div class="flex justify-between py-2 border-b">
                                    <dt class="text-sm text-gray-600">Passing Marks</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $result->exam->passing_marks }}</dd>
                                </div>
                                <div class="flex justify-between py-2">
                                    <dt class="text-sm font-semibold text-gray-700">Result</dt>
                                    <dd class="text-sm font-bold {{ $result->result_status == 'pass' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ ucfirst($result->result_status) }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                {{-- Section-wise Performance --}}
                @if($result->section_scores)
                    <div class="border-t pt-6 mt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Section-wise Performance</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Attempted</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Correct</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Marks</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Performance</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach(json_decode($result->section_scores, true) as $section => $scores)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ ucfirst($section) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                {{ $scores['attempted'] ?? 0 }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                {{ $scores['correct'] ?? 0 }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                {{ $scores['marks'] ?? 0 }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                {{ $scores['percentage'] ?? 0 }}%
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                    <div class="h-2.5 rounded-full 
                                                        @if(($scores['percentage'] ?? 0) >= 70) bg-green-600
                                                        @elseif(($scores['percentage'] ?? 0) >= 50) bg-yellow-600
                                                        @else bg-red-600
                                                        @endif" 
                                                        style="width: {{ $scores['percentage'] ?? 0 }}%">
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Performance Chart --}}
                <div class="border-t pt-6 mt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Performance Analysis</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Accuracy Chart --}}
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Answer Accuracy</h4>
                            <div class="relative pt-1">
                                <canvas id="accuracyChart" width="200" height="200"></canvas>
                            </div>
                        </div>

                        {{-- Time Analysis --}}
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Time Management</h4>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-xs text-gray-600">Total Time Allocated</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $result->exam->duration_minutes }} minutes</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-600">Time Taken</dt>
                                    <dd class="text-sm font-medium text-gray-900">
                                        {{ floor($result->response->time_spent_seconds / 60) }} minutes
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-600">Average Time per Question</dt>
                                    <dd class="text-sm font-medium text-gray-900">
                                        {{ $result->total_questions_attempted > 0 ? 
                                           round($result->response->time_spent_seconds / $result->total_questions_attempted) : 0 }} seconds
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-600">Submission Status</dt>
                                    <dd class="text-sm font-medium {{ $result->response->status == 'submitted' ? 'text-green-600' : 'text-yellow-600' }}">
                                        {{ $result->response->status == 'submitted' ? 'Manually Submitted' : 'Auto-Submitted' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                {{-- Comparison with Others --}}
                @if($statistics)
                    <div class="border-t pt-6 mt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Comparative Analysis</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-center">
                                <p class="text-sm text-gray-600 mb-2">Average Score</p>
                                <p class="text-2xl font-bold text-gray-900">{{ round($statistics->average_score, 1) }}</p>
                                <p class="text-xs text-gray-500">
                                    Your Score: {{ $result->final_score }}
                                    @if($result->final_score > $statistics->average_score)
                                        <span class="text-green-600">(+{{ round($result->final_score - $statistics->average_score, 1) }})</span>
                                    @else
                                        <span class="text-red-600">({{ round($result->final_score - $statistics->average_score, 1) }})</span>
                                    @endif
                                </p>
                            </div>

                            <div class="text-center">
                                <p class="text-sm text-gray-600 mb-2">Highest Score</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $statistics->highest_score }}</p>
                                <p class="text-xs text-gray-500">
                                    Gap: {{ $statistics->highest_score - $result->final_score }} marks
                                </p>
                            </div>

                            <div class="text-center">
                                <p class="text-sm text-gray-600 mb-2">Pass Percentage</p>
                                <p class="text-2xl font-bold text-gray-900">{{ round($statistics->pass_percentage, 1) }}%</p>
                                <p class="text-xs text-gray-500">
                                    Total Appeared: {{ $statistics->total_appeared }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Action Buttons --}}
                <div class="border-t pt-6 mt-6">
                    <div class="flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
                        <div class="flex space-x-3">
                            <a href="{{ route('exams.result.download', $result->id) }}" 
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Download Scorecard
                            </a>

                            @if($result->exam->allow_result_review)
                                <a href="{{ route('exams.answer-key', $result->exam->id) }}" 
                                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    View Answer Key
                                </a>
                            @endif

                            <button onclick="window.print()" 
                                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                Print
                            </button>
                        </div>

                        @if($nextSteps)
                            <a href="{{ $nextSteps['url'] }}" 
                               class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-green-700">
                                {{ $nextSteps['label'] }} →
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Certificate (if qualified) --}}
        @if($result->is_qualified && $certificate)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Certificate of Achievement</h3>
                    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg p-6 text-center">
                        <svg class="mx-auto h-16 w-16 text-indigo-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                        <p class="text-lg font-medium text-gray-900 mb-2">
                            Certificate Number: {{ $certificate->certificate_number }}
                        </p>
                        <p class="text-sm text-gray-600 mb-4">
                            This certifies that you have successfully qualified in the {{ $result->exam->exam_name }}
                        </p>
                        <a href="{{ route('exams.certificate.download', $certificate->id) }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Download Certificate
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Accuracy Chart
    const ctx = document.getElementById('accuracyChart').getContext('2d');
    const accuracyChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Correct', 'Wrong', 'Unanswered'],
            datasets: [{
                data: [
                    {{ $result->correct_answers }},
                    {{ $result->wrong_answers }},
                    {{ $result->unanswered }}
                ],
                backgroundColor: [
                    'rgb(34, 197, 94)',
                    'rgb(239, 68, 68)',
                    'rgb(156, 163, 175)'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
</script>
@endpush

@push('styles')
<style>
    @media print {
        .no-print {
            display: none !important;
        }
    }
</style>
@endpush
@endsection