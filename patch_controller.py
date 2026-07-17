import re

with open('app/Http/Controllers/InterviewEvaluationController.php', 'r') as f:
    content = f.read()

# I need to update the calculation logic in store()
old_calc = """        // 1. Appearance (1 item) - max 4
        $appSum = (int)($r['appearance_1'] ?? 0);
        $appScore = ($appSum / 4) * $wApp;

        // 2. Knowledge (4 items) - max 16
        $knowSum = (int)($r['knowledge_1'] ?? 0) + (int)($r['knowledge_2'] ?? 0) + (int)($r['knowledge_3'] ?? 0) + (int)($r['knowledge_4'] ?? 0);
        $knowScore = ($knowSum / 16) * $wKno;

        // 3. Communication (3 items) - max 12
        $commSum = (int)($r['comm_1'] ?? 0) + (int)($r['comm_2'] ?? 0) + (int)($r['comm_3'] ?? 0);
        $commScore = ($commSum / 12) * $wCom;

        // 4. Other (10 items) - max 40
        $othSum = 0;
        for ($i = 1; $i <= 10; $i++) {
            $othSum += (int)($r['other_'.$i] ?? 0);
        }
        $othScore = ($othSum / 40) * $wOth;"""

new_calc = """        // Check if using new 13-item 1-5 format or old 18-item 1-4 format
        $isNewFormat = isset($r['other_5']) && !isset($r['other_6']);
        $maxApp = $isNewFormat ? 5 : 4;
        $maxKnow = $isNewFormat ? 20 : 16;
        $maxComm = $isNewFormat ? 15 : 12;
        $maxOth = $isNewFormat ? 25 : 40;
        $othCount = $isNewFormat ? 5 : 10;

        // 1. Appearance (1 item)
        $appSum = (int)($r['appearance_1'] ?? 0);
        $appScore = ($appSum / $maxApp) * $wApp;

        // 2. Knowledge (4 items)
        $knowSum = (int)($r['knowledge_1'] ?? 0) + (int)($r['knowledge_2'] ?? 0) + (int)($r['knowledge_3'] ?? 0) + (int)($r['knowledge_4'] ?? 0);
        $knowScore = ($knowSum / $maxKnow) * $wKno;

        // 3. Communication (3 items)
        $commSum = (int)($r['comm_1'] ?? 0) + (int)($r['comm_2'] ?? 0) + (int)($r['comm_3'] ?? 0);
        $commScore = ($commSum / $maxComm) * $wCom;

        // 4. Other
        $othSum = 0;
        for ($i = 1; $i <= $othCount; $i++) {
            $othSum += (int)($r['other_'.$i] ?? 0);
        }
        $othScore = ($othSum / $maxOth) * $wOth;"""

content = content.replace(old_calc, new_calc)

old_redirect = """        return redirect()->route('recruitment.hrmpsb.interview.matrix', $applicant->id)
            ->with('success', 'Your interview evaluation has been saved successfully.');"""

new_redirect = """        if ($request->has('redirect_back')) {
            return back()->with('success', 'Your interview evaluation has been saved successfully.');
        }
        
        return redirect()->route('recruitment.hrmpsb.interview.matrix', $applicant->id)
            ->with('success', 'Your interview evaluation has been saved successfully.');"""

content = content.replace(old_redirect, new_redirect)

with open('app/Http/Controllers/InterviewEvaluationController.php', 'w') as f:
    f.write(content)
