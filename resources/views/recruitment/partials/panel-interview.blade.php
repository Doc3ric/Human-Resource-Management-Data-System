<div class="card shadow-sm border-0 h-100 d-flex flex-column">
    <div class="card-header fw-bold text-white d-flex justify-content-between align-items-center" style="background-color: #0dcaf0; border-top-left-radius: 8px; border-top-right-radius: 8px;">
        <span>Interview Evaluation for {{ $applicant->ain ?? $applicant->id }}</span>
    </div>
    
    <div class="card-body p-4" style="overflow-y: auto; flex: 1;">
        <form method="POST" action="{{ route('recruitment.hrmpsb.interview.store') }}" id="interviewEvaluationForm">
            <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">
            <input type="hidden" name="redirect_back" value="1">
            @csrf
            
            {{-- SECTION I --}}
            <div class="bg-dark text-white px-3 py-2 rounded mb-3 fw-bold shadow-sm" style="border-left: 4px solid #0dcaf0;">
                I. Personal Appearance (5%)
            </div>
            <div class="p-3 rounded shadow-sm border mb-4" style="background-color: rgba(128, 128, 128, 0.04);">
                <div class="d-flex align-items-center mb-2">
                    <div class="small flex-grow-1 pe-1" style="line-height: 1.3; opacity: 0.95;">
                        1. The candidate presents himself/herself in a good grooming and tidy appearance.
                    </div>
                    <div class="flex-shrink-0">
                        <select name="ratings[appearance_1]" class="form-select form-select-sm text-center fw-bold px-1" style="width: 45px; cursor: pointer; text-align-last: center;">
                            <option value="">-</option>
                            <option value="4">4</option>
                            <option value="3">3</option>
                            <option value="2">2</option>
                            <option value="1">1</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- SECTION II --}}
            <div class="bg-dark text-white px-3 py-2 rounded mb-3 fw-bold shadow-sm" style="border-left: 4px solid #198754;">
                II. Knowledge on the Job & Org (50%)
            </div>
            <div class="p-3 rounded shadow-sm border mb-4" style="background-color: rgba(128, 128, 128, 0.04);">
                <!-- 1 -->
                <div class="d-flex align-items-center mb-3 pb-2 border-bottom" style="border-color: rgba(128, 128, 128, 0.15) !important;">
                    <div class="small flex-grow-1 pe-1" style="line-height: 1.3; opacity: 0.95;">
                        1. The candidate is knowledgeable of the functions of the vacant position.
                    </div>
                    <div class="flex-shrink-0">
                        <select name="ratings[knowledge_1]" class="form-select form-select-sm text-center fw-bold px-1" style="width: 45px; cursor: pointer; text-align-last: center;">
                            <option value="">-</option>
                            <option value="4">4</option>
                            <option value="3">3</option>
                            <option value="2">2</option>
                            <option value="1">1</option>
                        </select>
                    </div>
                </div>
                <!-- 2 -->
                <div class="d-flex align-items-center mb-3 pb-2 border-bottom" style="border-color: rgba(128, 128, 128, 0.15) !important;">
                    <div class="small flex-grow-1 pe-1" style="line-height: 1.3; opacity: 0.95;">
                        2. The candidate is knowledgeable of the organizational structure of the department/division where the vacancy exists.
                    </div>
                    <div class="flex-shrink-0">
                        <select name="ratings[knowledge_2]" class="form-select form-select-sm text-center fw-bold px-1" style="width: 45px; cursor: pointer; text-align-last: center;">
                            <option value="">-</option>
                            <option value="4">4</option>
                            <option value="3">3</option>
                            <option value="2">2</option>
                            <option value="1">1</option>
                        </select>
                    </div>
                </div>
                <!-- 3 -->
                <div class="d-flex align-items-center mb-3 pb-2 border-bottom" style="border-color: rgba(128, 128, 128, 0.15) !important;">
                    <div class="small flex-grow-1 pe-1" style="line-height: 1.3; opacity: 0.95;">
                        3. The candidate is knowledgeable of the mission and vision of the department/office where the vacancy exists.
                    </div>
                    <div class="flex-shrink-0">
                        <select name="ratings[knowledge_3]" class="form-select form-select-sm text-center fw-bold px-1" style="width: 45px; cursor: pointer; text-align-last: center;">
                            <option value="">-</option>
                            <option value="4">4</option>
                            <option value="3">3</option>
                            <option value="2">2</option>
                            <option value="1">1</option>
                        </select>
                    </div>
                </div>
                <!-- 4 -->
                <div class="d-flex align-items-center mb-2">
                    <div class="small flex-grow-1 pe-1" style="line-height: 1.3; opacity: 0.95;">
                        4. The candidate can explain how the functions of the vacant position translate to the achievement of the mission and vision.
                    </div>
                    <div class="flex-shrink-0">
                        <select name="ratings[knowledge_4]" class="form-select form-select-sm text-center fw-bold px-1" style="width: 45px; cursor: pointer; text-align-last: center;">
                            <option value="">-</option>
                            <option value="4">4</option>
                            <option value="3">3</option>
                            <option value="2">2</option>
                            <option value="1">1</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- SECTION III --}}
            <div class="bg-dark text-white px-3 py-2 rounded mb-3 fw-bold shadow-sm" style="border-left: 4px solid #0dcaf0;">
                III. Communication / Interpersonal (10%)
            </div>
            <div class="p-3 rounded shadow-sm border mb-4" style="background-color: rgba(128, 128, 128, 0.04);">
                <!-- 1 -->
                <div class="d-flex align-items-center mb-3 pb-2 border-bottom" style="border-color: rgba(128, 128, 128, 0.15) !important;">
                    <div class="small flex-grow-1 pe-1" style="line-height: 1.3; opacity: 0.95;">
                        1. Listens to questions attentively and actively.
                    </div>
                    <div class="flex-shrink-0">
                        <select name="ratings[comm_1]" class="form-select form-select-sm text-center fw-bold px-1" style="width: 45px; cursor: pointer; text-align-last: center;">
                            <option value="">-</option>
                            <option value="4">4</option>
                            <option value="3">3</option>
                            <option value="2">2</option>
                            <option value="1">1</option>
                        </select>
                    </div>
                </div>
                <!-- 2 -->
                <div class="d-flex align-items-center mb-3 pb-2 border-bottom" style="border-color: rgba(128, 128, 128, 0.15) !important;">
                    <div class="small flex-grow-1 pe-1" style="line-height: 1.3; opacity: 0.95;">
                        2. Answers the questions or expresses ideas clearly, concisely and logically.
                    </div>
                    <div class="flex-shrink-0">
                        <select name="ratings[comm_2]" class="form-select form-select-sm text-center fw-bold px-1" style="width: 45px; cursor: pointer; text-align-last: center;">
                            <option value="">-</option>
                            <option value="4">4</option>
                            <option value="3">3</option>
                            <option value="2">2</option>
                            <option value="1">1</option>
                        </select>
                    </div>
                </div>
                <!-- 3 -->
                <div class="d-flex align-items-center mb-2">
                    <div class="small flex-grow-1 pe-1" style="line-height: 1.3; opacity: 0.95;">
                        3. Demonstrates confidence by displaying positive body language and maintaining eye contact.
                    </div>
                    <div class="flex-shrink-0">
                        <select name="ratings[comm_3]" class="form-select form-select-sm text-center fw-bold px-1" style="width: 45px; cursor: pointer; text-align-last: center;">
                            <option value="">-</option>
                            <option value="4">4</option>
                            <option value="3">3</option>
                            <option value="2">2</option>
                            <option value="1">1</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- SECTION IV --}}
            <div class="bg-dark text-white px-3 py-2 rounded mb-3 fw-bold shadow-sm" style="border-left: 4px solid #ffc107;">
                IV. Other Evaluation Criteria (35%)
            </div>
            <div class="p-3 rounded shadow-sm border mb-4" style="background-color: rgba(128, 128, 128, 0.04);">
                <!-- 1 -->
                <div class="d-flex align-items-center mb-3 pb-2 border-bottom" style="border-color: rgba(128, 128, 128, 0.15) !important;">
                    <div class="small flex-grow-1 pe-1" style="line-height: 1.3; opacity: 0.95;">
                        <strong style="opacity: 1;">JOB COMMITMENT:</strong> Responsibility towards the mission and goals of an organization.
                    </div>
                    <div class="flex-shrink-0">
                        <select name="ratings[other_1]" class="form-select form-select-sm text-center fw-bold px-1" style="width: 45px; cursor: pointer; text-align-last: center;">
                            <option value="">-</option>
                            <option value="4">4</option>
                            <option value="3">3</option>
                            <option value="2">2</option>
                            <option value="1">1</option>
                        </select>
                    </div>
                </div>
                <!-- 2 -->
                <div class="d-flex align-items-center mb-3 pb-2 border-bottom" style="border-color: rgba(128, 128, 128, 0.15) !important;">
                    <div class="small flex-grow-1 pe-1" style="line-height: 1.3; opacity: 0.95;">
                        <strong style="opacity: 1;">COMMITMENT:</strong> Psychological attachment to the organization.
                    </div>
                    <div class="flex-shrink-0">
                        <select name="ratings[other_2]" class="form-select form-select-sm text-center fw-bold px-1" style="width: 45px; cursor: pointer; text-align-last: center;">
                            <option value="">-</option>
                            <option value="4">4</option>
                            <option value="3">3</option>
                            <option value="2">2</option>
                            <option value="1">1</option>
                        </select>
                    </div>
                </div>
                <!-- 3 -->
                <div class="d-flex align-items-center mb-3 pb-2 border-bottom" style="border-color: rgba(128, 128, 128, 0.15) !important;">
                    <div class="small flex-grow-1 pe-1" style="line-height: 1.3; opacity: 0.95;">
                        <strong style="opacity: 1;">POTENTIAL:</strong> Capability to perform duties of the position and higher ones.
                    </div>
                    <div class="flex-shrink-0">
                        <select name="ratings[other_3]" class="form-select form-select-sm text-center fw-bold px-1" style="width: 45px; cursor: pointer; text-align-last: center;">
                            <option value="">-</option>
                            <option value="4">4</option>
                            <option value="3">3</option>
                            <option value="2">2</option>
                            <option value="1">1</option>
                        </select>
                    </div>
                </div>
                <!-- 4 -->
                <div class="d-flex align-items-center mb-3 pb-2 border-bottom" style="border-color: rgba(128, 128, 128, 0.15) !important;">
                    <div class="small flex-grow-1 pe-1" style="line-height: 1.3; opacity: 0.95;">
                        <strong style="opacity: 1;">SINCERITY:</strong> Assessment of honesty, expression of valid/useful opinion backed by evidence.
                    </div>
                    <div class="flex-shrink-0">
                        <select name="ratings[other_4]" class="form-select form-select-sm text-center fw-bold px-1" style="width: 45px; cursor: pointer; text-align-last: center;">
                            <option value="">-</option>
                            <option value="4">4</option>
                            <option value="3">3</option>
                            <option value="2">2</option>
                            <option value="1">1</option>
                        </select>
                    </div>
                </div>
                <!-- 5 -->
                <div class="d-flex align-items-center mb-3 pb-2 border-bottom" style="border-color: rgba(128, 128, 128, 0.15) !important;">
                    <div class="small flex-grow-1 pe-1" style="line-height: 1.3; opacity: 0.95;">
                        <strong style="opacity: 1;">PROFESSIONALISM:</strong> Demonstrates consideration, respect, loyalty and exceeds expectations.
                    </div>
                    <div class="flex-shrink-0">
                        <select name="ratings[other_5]" class="form-select form-select-sm text-center fw-bold px-1" style="width: 45px; cursor: pointer; text-align-last: center;">
                            <option value="">-</option>
                            <option value="4">4</option>
                            <option value="3">3</option>
                            <option value="2">2</option>
                            <option value="1">1</option>
                        </select>
                    </div>
                </div>
                <!-- 6 -->
                <div class="d-flex align-items-center mb-3 pb-2 border-bottom" style="border-color: rgba(128, 128, 128, 0.15) !important;">
                    <div class="small flex-grow-1 pe-1" style="line-height: 1.3; opacity: 0.95;">
                        <strong style="opacity: 1;">INITIATIVE:</strong> Eagerness to start actions without being told to start them.
                    </div>
                    <div class="flex-shrink-0">
                        <select name="ratings[other_6]" class="form-select form-select-sm text-center fw-bold px-1" style="width: 45px; cursor: pointer; text-align-last: center;">
                            <option value="">-</option>
                            <option value="4">4</option>
                            <option value="3">3</option>
                            <option value="2">2</option>
                            <option value="1">1</option>
                        </select>
                    </div>
                </div>
                <!-- 7 -->
                <div class="d-flex align-items-center mb-3 pb-2 border-bottom" style="border-color: rgba(128, 128, 128, 0.15) !important;">
                    <div class="small flex-grow-1 pe-1" style="line-height: 1.3; opacity: 0.95;">
                        <strong style="opacity: 1;">TEAMWORK:</strong> Active involvement to a team resulting in goal achievement.
                    </div>
                    <div class="flex-shrink-0">
                        <select name="ratings[other_7]" class="form-select form-select-sm text-center fw-bold px-1" style="width: 45px; cursor: pointer; text-align-last: center;">
                            <option value="">-</option>
                            <option value="4">4</option>
                            <option value="3">3</option>
                            <option value="2">2</option>
                            <option value="1">1</option>
                        </select>
                    </div>
                </div>
                <!-- 8 -->
                <div class="d-flex align-items-center mb-3 pb-2 border-bottom" style="border-color: rgba(128, 128, 128, 0.15) !important;">
                    <div class="small flex-grow-1 pe-1" style="line-height: 1.3; opacity: 0.95;">
                        <strong style="opacity: 1;">TIME MANAGEMENT:</strong> Act of planning time spent on activities to increase productivity.
                    </div>
                    <div class="flex-shrink-0">
                        <select name="ratings[other_8]" class="form-select form-select-sm text-center fw-bold px-1" style="width: 45px; cursor: pointer; text-align-last: center;">
                            <option value="">-</option>
                            <option value="4">4</option>
                            <option value="3">3</option>
                            <option value="2">2</option>
                            <option value="1">1</option>
                        </select>
                    </div>
                </div>
                <!-- 9 -->
                <div class="d-flex align-items-center mb-3 pb-2 border-bottom" style="border-color: rgba(128, 128, 128, 0.15) !important;">
                    <div class="small flex-grow-1 pe-1" style="line-height: 1.3; opacity: 0.95;">
                        <strong style="opacity: 1;">CUSTOMER SERVICE:</strong> Act of taking care of customers needs by providing professional assistance.
                    </div>
                    <div class="flex-shrink-0">
                        <select name="ratings[other_9]" class="form-select form-select-sm text-center fw-bold px-1" style="width: 45px; cursor: pointer; text-align-last: center;">
                            <option value="">-</option>
                            <option value="4">4</option>
                            <option value="3">3</option>
                            <option value="2">2</option>
                            <option value="1">1</option>
                        </select>
                    </div>
                </div>
                <!-- 10 -->
                <div class="d-flex align-items-center mb-2">
                    <div class="small flex-grow-1 pe-1" style="line-height: 1.3; opacity: 0.95;">
                        <strong style="opacity: 1;">JOB SATISFACTION:</strong> Contentment of current job and the sense of accomplishment.
                    </div>
                    <div class="flex-shrink-0">
                        <select name="ratings[other_10]" class="form-select form-select-sm text-center fw-bold px-1" style="width: 45px; cursor: pointer; text-align-last: center;">
                            <option value="">-</option>
                            <option value="4">4</option>
                            <option value="3">3</option>
                            <option value="2">2</option>
                            <option value="1">1</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="p-3 rounded shadow-sm border mt-4 mb-3" style="background-color: rgba(128, 128, 128, 0.04);">
                <label for="evaluationRemarks" class="form-label fw-bold mb-2" style="font-size: 13px;">Remarks (Optional)</label>
                <textarea class="form-control form-control-sm" id="evaluationRemarks" name="remarks" rows="3" placeholder="Additional observations..."></textarea>
            </div>
            
        </form>
    </div>
    
    <div class="card-footer d-flex justify-content-end align-items-center py-3 border-top gap-2" style="background-color: transparent;">
        <form method="POST" action="{{ route('recruitment.deliberation.phase', $applicant) }}" class="m-0 me-auto">
            @csrf
            <input type="hidden" name="deliberation_phase" value="completed">
            <button type="submit" class="btn btn-outline-secondary btn-sm fw-bold px-4">Close &amp; Mark Completed</button>
        </form>
        <button type="button" class="btn btn-secondary btn-sm fw-bold px-4" onclick="document.getElementById('interviewEvaluationForm').reset()">Reset</button>
        <button type="submit" form="interviewEvaluationForm" class="btn btn-primary btn-sm fw-bold px-4" style="background-color: #0d6efd;">Save Evaluation</button>
    </div>
</div>
