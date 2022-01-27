public function actionImport()
{       
    $data2 = file_get_contents('/app/runtime/files/2.json');
    $data2 = json_decode($data2, true);

    $data1 = file_get_contents('/app/runtime/files/1.json');
    $data1 = json_decode($data1, true);

    if (empty($data1)) {
        echo "not data to process \n";
        return;
    }

    $mappedData2 = [];
    foreach ($data2 ?? [] as $idx => $item) {
        // indexing data from 2nd file to provide target access
        $mappedData2[$item['id']] = $item;
    }
    unset($data2);

    $dataToUpsert = [];
    foreach ($data1 as $item) {
        if ($item['status'] !== 'need_to_update') {
            continue;
        }

        if (isset($mappedData2[$item['id']]['counter'])) {
            $item['counter'] += $mappedData2[$item['id']]['counter'];
        }

        $dataToUpsert[] = $item;
    }
    unset($data1);

    if (empty($dataToUpsert)) {
        echo "no data to upsert \n";
        return;
    }

$sql = <<<sql
BEGIN TRANSACTION;

UPDATE test
    SET test.counter = test.counter + temp.counter  
FROM bd.tbl_test as test
    INNER JOIN bd.temp AS temp ON temp.id = test.id;

INSERT bd.tbl_test(id, status, counter)
SELECT id, status, counter FROM bd.temp AS temp
WHERE NOT EXISTS (
    SELECT 1 FROM tbl_test WHERE temp.id = tbl_test.id
);

COMMIT TRANSACTION;
sql;

    var_dump([
        'sql' => $sql,
        'countDataToUpsert' => count($dataToUpsert),
    ]);
}