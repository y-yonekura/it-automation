<?php
//   Copyright 2020 NEC Corporation
//
//   Licensed under the Apache License, Version 2.0 (the "License");
//   you may not use this file except in compliance with the License.
//   You may obtain a copy of the License at
//
//       http://www.apache.org/licenses/LICENSE-2.0
//
//   Unless required by applicable law or agreed to in writing, software
//   distributed under the License is distributed on an "AS IS" BASIS,
//   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
//   See the License for the specific language governing permissions and
//   limitations under the License.
//
//////////////////////////////////////////////////////////////////////
//  【処理概要】
//    ・本体tableのPKをtext(varchar等)にする
//    ・journal tableを使わないがcolumn class自体はdummyで存在させる(journalTableへの更新系でのSQLはskipする)
//    ・start transaction直後のsequenceへのselect .. for updateをskipする
//////////////////////////////////////////////////////////////////////

class RowIdentifyTextColumn extends TextColumn {
    protected $uniqueColumns;
    protected $strSequenceId;

    //----ここから継承メソッドの上書き処理

    function __construct ($strColId, $strColLabel, $strSequenceId=null, $uniqueColumns=[]) {
        global $g;
        
        parent::__construct($strColId, $strColLabel, $strSequenceId);
        $this->strSequenceId = $strSequenceId;
        $this->setHiddenMainTableColumn(true);
        $this->setHeader(true);

        $outputType = new OutputType(new ReqTabHFmt(), new TextTabBFmt());
        $this->setOutputType("update_table", $outputType);
        //自動入力
        $outputType = new OutputType(new ReqTabHFmt(), new StaticTextTabBFmt($g['objMTS']->getSomeMessage("ITAWDCH-STD-11401")));
        $this->setOutputType("register_table", $outputType);

        //----このインスタンスに紐づくOutputTypeインスタンスにアクセスする
        $this->getOutputType("delete_table")->init($this, "delete_table");
        $this->getOutputType("filter_table")->init($this, "filter_table");
        $this->getOutputType("print_table")->init($this, "print_table");
        //このインスタンスに紐づくOutputTypeインスタンスにアクセスする----

        $this->setDescription($g['objMTS']->getSomeMessage("ITAWDCH-STD-11402"));

        $this->setValidator(new SingleTextValidator(0,256));
    }

    //----AddColumnイベント系
    function initTable ($objTable, $colNo=null) {
        parent::initTable($objTable, $colNo);
    }
    //AddColumnイベント系----

    //----TableIUDイベント系
    public function beforeIUDValidateCheck (&$exeQueryData, &$reqOrgData=[], &$aryVariant=[]) {
        $boolRet = false;
        $intErrorType = null;
        $aryErrMsgBody = [];
        $strErrMsg = "";
        $strErrorBuf = "";

        $modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
        if ($modeValue === "DTUP_singleRecRegister") {
            //----親クラス[AutoNumColumn]の同名関数を呼んで、その後作業
            $retArray = parent::beforeIUDValidateCheck($exeQueryData, $reqOrgData, $aryVariant);
            //親クラス[AutoNumColumn]の同名関数を呼んで、その後作業----
        } else if ($modeValue === "DTUP_singleRecUpdate") {
            //----更新の場合
            $boolRet = true;
            $retArray = [$boolRet, $intErrorType, $aryErrMsgBody, $strErrMsg, $strErrorBuf];
            //更新の場合----
        } else if ($modeValue === "DTUP_singleRecDelete") {
            //----廃止の場合
            $boolRet = true;
            $retArray = [$boolRet, $intErrorType, $aryErrMsgBody, $strErrMsg, $strErrorBuf];
            //廃止の場合----
        }
        return $retArray;
    }
    //TableIUDイベント系----

    //ここまで継承メソッドの上書き処理----

    //----ここから新規メソッドの定義宣言処理
    public function setSequenceID ($strSequenceId) {
        $this->strSequenceId = $strSequenceId;
    }
    function getSequenceID() {
        return $this->strSequenceId;
    }
    //ここまで新規メソッドの定義宣言処理----
}

class JournalSeqNoColumnDummy extends TextColumn {
    global $g;
    //通常時は表示しない

    protected $strSequenceId;

    //----ここから継承メソッドの上書き処理

    function __construct ($strColId="JOURNAL_SEQ_NO", $strColExplain="", $strSequenceId=null) {
        if ($strColExplain === "") {
            $strColExplain = $g['objMTS']->getSomeMessage("ITAWDCH-STD-11301");
        }
        parent::__construct($strColId, $strColExplain);
        $this->setNum(true);
        $this->setSubtotalFlag(false);
        $this->setHeader(true);
        $this->setDBColumn(false);
        $this->getOutputType("print_journal_table")->setVisible(true);
        $this->getOutputType("update_table")->setVisible(false);
        $this->getOutputType("register_table")->setVisible(false);
        $this->getOutputType("filter_table")->setVisible(false);
        $this->getOutputType("print_table")->setVisible(false);
        $this->getOutputType("delete_table")->setVisible(false);
        $this->getOutputType("excel")->setVisible(false);
        $this->getOutputType("csv")->setVisible(false);
        $this->getOutputType("json")->setVisible(false);

        $this->setSequenceID($strSequenceId);
        //$this->setNumberSepaMarkShow(false);
    }

    //----FixColumnイベント系
    function afterFixColumn() {
        if ($this->getSequenceID() === null) {
            $arrayColumn = $this->objTable->getColumns();
            $objRIColumnID = $arrayColumn[$this->objTable->getRowIdentifyColumnID()];
            $strSeqId = $objRIColumnID->getSequenceID();
            if ($strSeqId !== "") {
                $this->setSequenceID("J".$strSeqId);
            }
        }
    }
    //FixColumnイベント系----

    //----TableIUDイベント系
    
    /* start transaction 直後のselect .. for updateをskip
    function getSequencesForTrzStart (&$arySequence=array()) {
    }
    */
    /* journal tableへの 事前select .. for updateをskip
    public function inTrzBeforeTableIUDAction (&$exeQueryData, &$reqOrgData=[], &$aryVariant=[]) {
    }
    */
    /* journal tableへのinsertをskip
    function inTrzAfterTableIUDAction(&$exeQueryData, &$reqOrgData=[], &$aryVariant=[]) {
    }
    */
    //TableIUDイベント系----

    //ここまで継承メソッドの上書き処理----

    //----ここから新規メソッドの定義宣言処理

    //NEW[1]
    function setSequenceID ($strSequenceId) {
        $this->strSequenceId = $strSequenceId;
    }

    //NEW[2]
    function getSequenceID() {
        return $this->strSequenceId;
    }
    //ここまで新規メソッドの定義宣言処理----
}

