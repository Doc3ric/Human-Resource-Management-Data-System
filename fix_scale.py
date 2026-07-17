import re

# 1. Update the Blade template to remove option 5
with open('resources/views/recruitment/partials/panel-interview.blade.php', 'r') as f:
    content = f.read()

# Specifically replace the option lines with lots of whitespace just in case
content = re.sub(r'[ \t]*<option value="5">5</option>[\r\n]+', '', content)

with open('resources/views/recruitment/partials/panel-interview.blade.php', 'w') as f:
    f.write(content)

# 2. Update the Controller logic
with open('app/Http/Controllers/InterviewEvaluationController.php', 'r') as f:
    controller_content = f.read()

old_logic = """        // Check if using new 13-item 1-5 format or old 18-item 1-4 format
        $isNewFormat = isset($r['other_5']) && !isset($r['other_6']);
        $maxApp = $isNewFormat ? 5 : 4;
        $maxKnow = $isNewFormat ? 20 : 16;
        $maxComm = $isNewFormat ? 15 : 12;
        $maxOth = $isNewFormat ? 25 : 40;
        $othCount = $isNewFormat ? 5 : 10;"""

new_logic = """        // Check if using new 13-item 1-4 format or old 18-item 1-4 format
        $isNewFormat = isset($r['other_5']) && !isset($r['other_6']);
        $maxApp = 4; // Always 4 for Appearance (1 question)
        $maxKnow = 16; // Always 16 for Knowledge (4 questions)
        $maxComm = 12; // Always 12 for Communication (3 questions)
        $maxOth = $isNewFormat ? 20 : 40; // 5 questions vs 10 questions
        $othCount = $isNewFormat ? 5 : 10;"""

controller_content = controller_content.replace(old_logic, new_logic)

with open('app/Http/Controllers/InterviewEvaluationController.php', 'w') as f:
    f.write(controller_content)
