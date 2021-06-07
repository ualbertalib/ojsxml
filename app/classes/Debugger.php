<?php


namespace OJSXml;


class Debugger {

    /** @var DBManager $_dbManager */
    private $_dbManager ;
    /** @var string $_sourceDir */
    private $_sourceDir;

    /**
     * Debugger constructor.
     *
     * @param DBManager $dbManager
     * @param string $sourceDir
     */
    public function __construct() {
        $this->_sourceDir = '/Users/erikhanson/files/ojsxml/docroot/csv/geus_import_files/';
    }

    public function execute() {
        echo "Searching for abstract with mismatched tags..." . PHP_EOL
            . "----------------------------------------" . PHP_EOL;
        $journalPaths = [
            'bullggu',
            'dgu_rk_1',
            'dgu_rk_2',
            'dgu_rk_3',
            'dgu_rk_4',
            'dgu_rk_5',
            'dgu_series_a',
            'dgu_series_b',
            'dgu_series_c',
            'dgu_series_d',
            'rapggu',
        ];

        $data = [
            'total' => 0,
            'extraOpenTags' => 0,
            'extraCloseTags' => 0,
            'journals' => []
        ];

        foreach ($journalPaths as $journalPath) {
            $fullPath = $this->_sourceDir . $journalPath;
            $this->importFiles($fullPath);


            $data['journals'][$journalPath] = [
                'count' => 0,
                'extraOpenTags' => 0,
                'extraCloseTags' => 0,
                'items' => []
            ];

            $this->checkAbstractTagCount($fullPath, $data['journals'][$journalPath]);

            $data['total'] += $data['journals'][$journalPath]['count'];
            $data['extraOpenTags'] += $data['journals'][$journalPath]['extraOpenTags'];
            $data['extraCloseTags'] += $data['journals'][$journalPath]['extraCloseTags'];

            echo $journalPath . ': ' . $data['journals'][$journalPath]['count'] . ' article(s)'
                . ' -- (' . $data['journals'][$journalPath]['extraOpenTags'] . ' open'
                . ', ' .$data['journals'][$journalPath]['extraCloseTags'] . ' close)' . PHP_EOL;
        }

        foreach ($data['journals'] as $journal) {
            foreach ($journal['items'] as $item) {
                if ($item['openTagCount'] > $item['closeTagCount']) {
                    $data['extraOpenTags']++;
                } else if ($item['openTagCount'] < $item['closeTagCount']) {
                    $data['extraCloseTags']++;
                }

            }
        }

        $this->writeJsonToFile($data);

        echo "----------------------------------------" . PHP_EOL
            . "Total articles affected: " . $data['total']
            . ' -- (' . $data['extraOpenTags'] . ' open'
            . ', ' .$data['extraCloseTags'] . ' close)' . PHP_EOL;

    }

    private function importFiles($fullPath) {
        $this->_dbManager = new DBManager();
        $this->_dbManager->importIssueCsvData($fullPath . "/*");
    }

    private function checkAbstractTagCount($sourceDir, &$journalObject) {
        $resultSet = $this->_dbManager->getAllAbstracts();

        foreach ($resultSet as $item) {
            $abstract = $item['articleAbstract'];

            $openTagCount = substr_count($abstract, '<p>');
            $closeTagCount = substr_count($abstract, '</p>');

            if ($openTagCount != $closeTagCount) {
                $item['openTagCount'] = $openTagCount;
                $item['closeTagCount'] = $closeTagCount;

                if ($openTagCount > $closeTagCount) {
                    $journalObject['extraOpenTags']++;
                } else {
                    $journalObject['extraCloseTags']++;
                }
                array_push($journalObject['items'], $item);
            }
        }

        $journalObject['count'] = count($journalObject['items']);
    }

    /**
     * @param array $contents
     */
    private function writeJsonToFile($contents) {
        $jsonContents = json_encode($contents, JSON_PRETTY_PRINT);

        $file = fopen('/Users/erikhanson/files/ojsxml/docroot/temp/abstractData.json', 'w') or die('Unable to open file');
        fwrite($file, $jsonContents);
        fclose($file);
    }



}
