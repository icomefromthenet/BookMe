SET profiling=1;

SET @minslot = 4000000;
SET @maxslot = 5000000;


CALL bm_rules_timeslot_left_nodes(@minslot,@maxslot);
CALL bm_rules_timeslot_right_nodes(@minslot,@maxslot);

-- select * from timeslot_left_nodes_result;
-- select * from timeslot_right_nodes_result;



-- select count(*), min(m.opening_slot_id),max(m.closing_slot_id) from () as m;


    SELECT i.*
    FROM timeslot_slots i USE INDEX (`idx_timeslot_slots_ri_upper`)
    JOIN timeslot_left_nodes_result l ON i.node = l.node
    AND i.timeslot_id = 1
    AND i.closing_slot_id >= @minslot
    UNION ALL
    SELECT i.*
    FROM timeslot_slots i
    JOIN timeslot_right_nodes_result l ON i.node = l.node
    AND i.timeslot_id = 1
    AND i.opening_slot_id <= @maxslot
    UNION ALL
    SELECT i.*
    FROM timeslot_slots i
    WHERE node BETWEEN @minslot AND @maxslot
    AND i.timeslot_id = 1;


/*
select i.*
from timeslot_slots i 
where i.opening_slot_id <= @maxslot  AND i.closing_slot_id >  @minslot
AND i.timeslot_id = 1;
*/

SHOW profiles;