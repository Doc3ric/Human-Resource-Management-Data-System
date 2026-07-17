import re

with open('resources/views/recruitment/partials/panel-interview.blade.php', 'r') as f:
    content = f.read()

# Replace Item 5's mb-2 to add the border bottom, then append 6-10
item_5_original = """                <!-- 5 -->
                <div class="d-flex align-items-center mb-2">
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
            </div>"""

items_6_to_10 = """                <!-- 5 -->
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
            </div>"""

content = content.replace(item_5_original, items_6_to_10)

with open('resources/views/recruitment/partials/panel-interview.blade.php', 'w') as f:
    f.write(content)
