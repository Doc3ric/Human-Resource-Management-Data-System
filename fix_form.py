import re
with open('resources/views/recruitment/partials/panel-interview.blade.php', 'r') as f:
    content = f.read()

content = content.replace('scores[personal_appearance][1]', 'ratings[appearance_1]')
for i in range(1, 5):
    content = content.replace(f'scores[knowledge][{i}]', f'ratings[knowledge_{i}]')
for i in range(1, 4):
    content = content.replace(f'scores[communication][{i}]', f'ratings[comm_{i}]')
for i in range(1, 6):
    content = content.replace(f'scores[other][{i}]', f'ratings[other_{i}]')

content = content.replace('<form method="POST" action="#" id="interviewEvaluationForm">', '<form method="POST" action="{{ route(\'recruitment.hrmpsb.interview.store\') }}" id="interviewEvaluationForm">\n            <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">\n            <input type="hidden" name="redirect_back" value="1">')

content = content.replace('<button type="button" class="btn btn-primary btn-sm fw-bold px-4" onclick="alert(\'Interview evaluation saved!\')" style="background-color: #0d6efd;">Save Evaluation</button>', '<button type="submit" form="interviewEvaluationForm" class="btn btn-primary btn-sm fw-bold px-4" style="background-color: #0d6efd;">Save Evaluation</button>')

with open('resources/views/recruitment/partials/panel-interview.blade.php', 'w') as f:
    f.write(content)
