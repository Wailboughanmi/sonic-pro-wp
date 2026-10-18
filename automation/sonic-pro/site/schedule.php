<?php
/** Pure slot calculation, never exposes private event details. */
function sonic_slots(array $config, string $month, array $busy, ?DateTimeImmutable $now=null): array {
    $tz=new DateTimeZone($config['timezone']);
    $now=$now??new DateTimeImmutable('now',$tz);
    if(!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/',$month)) throw new InvalidArgumentException('Invalid month');
    $first=new DateTimeImmutable($month.'-01 00:00:00',$tz);
    $last=$first->modify('first day of next month');
    $earliest=$now->modify('+'.$config['notice_minutes'].' minutes');
    $latest=$now->modify('+'.$config['horizon_days'].' days');
    $slots=[];
    for($day=$first;$day<$last;$day=$day->modify('+1 day')) {
        $date=$day->format('Y-m-d');
        if(in_array($date,$config['closed_dates']??[],true)) continue;
        foreach($config['hours'][$day->format('N')]??[] as [$from,$to]) {
            $start=new DateTimeImmutable($date.' '.$from,$tz);
            $close=new DateTimeImmutable($date.' '.$to,$tz);
            for(;$start<$close;$start=$start->modify('+'.$config['duration_minutes'].' minutes')) {
                $end=$start->modify('+'.$config['duration_minutes'].' minutes');
                if($start<$earliest||$start>$latest||$end>$close) continue;
                $blocked=false;
                foreach($busy as $range) {
                    if($start<new DateTimeImmutable($range['end']) && $end>new DateTimeImmutable($range['start'])) {$blocked=true;break;}
                }
                if(!$blocked) $slots[$date][]=['start'=>$start->format(DATE_RFC3339),'end'=>$end->format(DATE_RFC3339),'label'=>$start->format('H:i')];
            }
        }
    }
    return $slots;
}
