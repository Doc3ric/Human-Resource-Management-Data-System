<?php
$res = \App\Models\Applicant::where('item_no', 'like', '%BPH%MAR%149%')->pluck('item_no')->unique()->toArray();
dump($res);
