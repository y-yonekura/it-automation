<?php
//   Copyright 2019 NEC Corporation
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
    $intControlDebugLevel01 = 50;

    $strFxName = __FUNCTION__;
    dev_log($g['objMTS']->getSomeMessage("ITAWDCH-STD-1",__FILE__),$intControlDebugLevel01);

    // ローカル変数宣言
    $str_temp   = "";

    // DBアクセスを伴う処理開始
    try{
        $objIntNumVali = new IntNumValidator(null,null,"","",array("NOT_NULL"=>true));
	    if( $objIntNumVali->isValid($p_role_id) === false ){
	        throw new Exception( '00000100-([FILE]' . __FILE__ . ',[LINE]' . __LINE__ . ')' );
	    }

        // ロール一覧(A_ROLE_LIST)が存在しているかチェック
        $sql = "SELECT DISUSE_FLAG
                FROM   A_ROLE_LIST
                WHERE  ROLE_ID = :ROLE_ID_BV
                AND DISUSE_FLAG IN ('0','1')";

        $tmpAryBind = array('ROLE_ID_BV'=>$p_role_id);
        $retArray = singleSQLExecuteAgent($sql, $tmpAryBind, $strFxName);
        if( $retArray[0] === true ){
            $intTmpRowCount=0;
            $showTgtRow = array();
            $objQuery =& $retArray[1];
            while($row = $objQuery->resultFetch() ){
                if($row !== false){
                    $intTmpRowCount+=1;
                }
                if($intTmpRowCount==1){
                    $showTgtRow = $row;
                }
            }
            $selectRowLength = $intTmpRowCount;
            if( $selectRowLength != 1 ){
                throw new Exception( '00000200-([FILE]' . __FILE__ . ',[LINE]' . __LINE__ . ')' );
            }
            unset($objQuery);
        }
        else{
            throw new Exception( '00000300-([FILE]' . __FILE__ . ',[LINE]' . __LINE__ . ')' );
        }

        $p_role_list_disuse_flag = $showTgtRow['DISUSE_FLAG'];

        // メンテナンスボタンの表示/非表示を切り替え
        if($p_role_list_disuse_flag === '0' ){
            $BG_COLOR = "";
        }
        else{
            $BG_COLOR = " class=\"disuse\" ";
        }

        // ログイン中のユーザのロールID取得
        $sql = "SELECT ROLE_ID
                FROM   D_ROLE_ACCOUNT_LINK_LIST
                WHERE  USER_ID = :USER_ID
                AND DISUSE_FLAG = '0'";

        $tmpAryBind = array('USER_ID'=>$g['login_id']);
        $retArray = singleSQLExecuteAgent($sql, $tmpAryBind, $strFxName);
        $role_id = array();
        if( $retArray[0] === true ){
            $objQuery =& $retArray[1];
            while($row = $objQuery->resultFetch() ){
                array_push($role_id, $row['ROLE_ID']);
            }
            unset($objQuery);
        }
        else{
            throw new Exception( '00000300-([FILE]' . __FILE__ . ',[LINE]' . __LINE__ . ')' );
        }

        $sql = "SELECT TAB_3.DISUSE_FLAG      AS MG_DISUSE_FLAG,
                        TAB_2.DISUSE_FLAG,
                        TAB_3.ACCESS_AUTH     AS MG_ACCESS_AUTH,
                        TAB_2.ACCESS_AUTH,
                        TAB_2.MENU_GROUP_ID,
                        TAB_3.MENU_GROUP_NAME,
                        TAB_2.DISP_SEQ,
                        TAB_1.MENU_ID,
                        TAB_2.MENU_NAME,
                        TAB_2.LOGIN_NECESSITY      AS LOGIN_NECESSITY_FLAG,
                        TAB_4.NAME                 AS LOGIN_NECESSITY_DISP,
                        TAB_1.PRIVILEGE            AS PRIVILEGE_FLAG,
                        TAB_5.NAME                 AS PRIVILEGE_TYPE_DISP
                FROM   A_ROLE_MENU_LINK_LIST            TAB_1
                        LEFT JOIN A_MENU_LIST            TAB_2 ON (TAB_1.MENU_ID = TAB_2.MENU_ID)
                        LEFT JOIN A_MENU_GROUP_LIST      TAB_3 ON (TAB_2.MENU_GROUP_ID = TAB_3.MENU_GROUP_ID)
                        LEFT JOIN A_LOGIN_NECESSITY_LIST TAB_4 ON (TAB_2.LOGIN_NECESSITY = TAB_4.FLAG)
                        LEFT JOIN A_PRIVILEGE_LIST       TAB_5 ON (TAB_1.PRIVILEGE = TAB_5.FLAG)
                WHERE  TAB_1.DISUSE_FLAG = '0'
                AND    TAB_1.ROLE_ID     = :ROLE_ID_BV
                ORDER BY TAB_2.MENU_GROUP_ID, TAB_2.DISP_SEQ ASC";

        $tmpAryBind = array('ROLE_ID_BV'=>$p_role_id);
        $retArray = singleSQLExecuteAgent($sql, $tmpAryBind, $strFxName);
        if( $retArray[0] === true ){
            $objQuery =& $retArray[1];
            $str_temp =
<<< EOD
                <div class="fakeContainer_Yobi3">
                <table id="DbTable_Yobi3">
                    <tr class="defaultExplainRow">
                        <th scope="col" onClick="tableSort(1, this, 'DbTable_Yobi3_data', 0, nsort);"  class="sort" ><span class="generalBold">{$g['objMTS']->getSomeMessage("ITAWDCH-MNU-1069053")}</span></th>
                        <th scope="col" onClick="tableSort(1, this, 'DbTable_Yobi3_data', 1       );"  class="sort" ><span class="generalBold">{$g['objMTS']->getSomeMessage("ITAWDCH-MNU-1030101")}</span></th>
                        <th scope="col" onClick="tableSort(1, this, 'DbTable_Yobi3_data', 1       );"  class="sort" ><span class="generalBold">{$g['objMTS']->getSomeMessage("ITAWDCH-MNU-1030201")}</span></th>
                        <th scope="col" onClick="tableSort(1, this, 'DbTable_Yobi3_data', 2, nsort);"  class="sort" ><span class="generalBold">{$g['objMTS']->getSomeMessage("ITAWDCH-MNU-1040901")}</span></th>
                        <th scope="col" onClick="tableSort(1, this, 'DbTable_Yobi3_data', 3, nsort);"  class="sort" ><span class="generalBold">{$g['objMTS']->getSomeMessage("ITAWDCH-MNU-1040101")}</span></th>
                        <th scope="col" onClick="tableSort(1, this, 'DbTable_Yobi3_data', 4       );"  class="sort" ><span class="generalBold">{$g['objMTS']->getSomeMessage("ITAWDCH-MNU-1040601")}</span></th>
                        <th scope="col" onClick="tableSort(1, this, 'DbTable_Yobi3_data', 5       );"  class="sort" ><span class="generalBold">{$g['objMTS']->getSomeMessage("ITAWDCH-MNU-1040701")}</span></th>
                        <th scope="col" onClick="tableSort(1, this, 'DbTable_Yobi3_data', 6       );"  class="sort" ><span class="generalBold">{$g['objMTS']->getSomeMessage("ITAWDCH-MNU-1041001")}</span></th>
                    </tr>
EOD;
            $output_str .= $str_temp;
            $num_rows = 0;
            $temp_no = 1;
            while ( $menu_row =  $objQuery->resultFetch() ){
                $num_rows += 1;
                // 項目生成
                $mg_role_id = explode("," , $menu_row['MG_ACCESS_AUTH']);
                $menu_role_id = explode("," , $menu_row['ACCESS_AUTH']);
                $COLUMN_00 = $temp_no;
                $COLUMN_07 = nl2br(htmlspecialchars($menu_row['MENU_GROUP_ID']));
                $COLUMN_01 = nl2br(htmlspecialchars($menu_row['MENU_GROUP_NAME']));
                $COLUMN_02 = nl2br(htmlspecialchars($menu_row['DISP_SEQ']));
                $COLUMN_03 = nl2br(htmlspecialchars($menu_row['MENU_ID']));
                $COLUMN_04 = nl2br(htmlspecialchars($menu_row['MENU_NAME']));
                $COLUMN_05 = nl2br(htmlspecialchars($menu_row['LOGIN_NECESSITY_DISP']));
                $COLUMN_06 = nl2br(htmlspecialchars($menu_row['PRIVILEGE_TYPE_DISP']));
                $url = "01_browse.php?no=2100000204&filter=on&Filter1Tbl_2=" .str_replace(" ","%20",$COLUMN_01);
                $url_02 = "01_browse.php?no=2100000205&filter=on&Filter1Tbl_1__S=" .str_replace(" ","%20",$COLUMN_03) ."&Filter1Tbl_1__E=" .str_replace(" ","%20",$COLUMN_03);
                $url_03 = "01_browse.php?no=2100000205&filter=on&Filter1Tbl_4=" .str_replace(" ","%20",$COLUMN_04);
                $url_04 = "01_browse.php?no=2100000204&filter=on&Filter1Tbl_1__S=" .str_replace(" ","%20",$COLUMN_07) ."&Filter1Tbl_1__E=" .str_replace(" ","%20",$COLUMN_07);
                $htmlText = "<td" .$BG_COLOR. "><a href=\"" .$url_04. "\"target=\"_blank\">" .$COLUMN_07. "</a></td>";
                $htmlText_02 = "<td" .$BG_COLOR. "><a href=\"" .$url. "\"target=\"_blank\">" .$COLUMN_01. "</a></td>";
                // 廃止判定
                if( $menu_row['MG_DISUSE_FLAG'] == '1' ){
                  $COLUMN_07 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-11101") . '(' . nl2br(htmlspecialchars($menu_row['MENU_GROUP_ID'])) . ')';
                  $COLUMN_01 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-11101") . '(' . nl2br(htmlspecialchars($menu_row['MENU_GROUP_ID'])) . ')';
                  $htmlText = "<td" .$BG_COLOR .">" .$COLUMN_07. "</td>";
                  $htmlText_02 = "<td" .$BG_COLOR .">" .$COLUMN_01. "</td>";
                }else{
                  // アクセス許可ロール判定
                  $auth_flag = '0';
                  foreach ($mg_role_id as $value) {
                    web_log("value:" .$value);
                    if($role_id == $value){
                      $auth_flag = '1';
                    }
                    if(empty($value)){
                      $auth_flag = '1';
                    }
                  }
                  if($auth_flag == '0'){
                    $COLUMN_07 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-11101") . '(' . nl2br(htmlspecialchars($menu_row['MENU_GROUP_ID'])) . ')';
                    $COLUMN_01 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-11101") . '(' . nl2br(htmlspecialchars($menu_row['MENU_GROUP_ID'])) . ')';
                    $htmlText = "<td" .$BG_COLOR .">" .$COLUMN_07. "</td>";
                    $htmlText_02 = "<td" .$BG_COLOR .">" .$COLUMN_01. "</td>";
                  }
                }
                $htmlText_03 = "<td class=\"number\"" .$BG_COLOR ."><a href=\"" .$url_02. "\"target=\"_blank\">" .$COLUMN_03. "</a></td>";
                $htmlText_04 = "<td" .$BG_COLOR. "><a href=\"" .$url_03. "\"target=\"_blank\">" .$COLUMN_04. "</a></td>";
                // 廃止判定
                if( $menu_row['DISUSE_FLAG'] == '1' ){
                  $COLUMN_03 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-11101") . '(' . nl2br(htmlspecialchars($menu_row['MENU_ID'])) . ')';
                  $COLUMN_04 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-11101") . '(' . nl2br(htmlspecialchars($menu_row['MENU_ID'])) . ')';
                  $htmlText_03 = "<td" .$BG_COLOR .">" .$COLUMN_03. "</td>";
                  $htmlText_04 = "<td" .$BG_COLOR .">" .$COLUMN_04. "</td>";
                }else{
                  // アクセス許可ロール判定
                  $auth_flag = '0';
                  foreach ($menu_role_id as $value) {
                    web_log("value:" .$value);
                    if($role_id == $value){
                      $auth_flag = '1';
                    }
                    if(empty($value)){
                      $auth_flag = '1';
                    }
                  }
                  if($auth_flag == '0'){
                    $COLUMN_03 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-11101") . '(' . nl2br(htmlspecialchars($menu_row['MENU_ID'])) . ')';
                    $COLUMN_04 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-11101") . '(' . nl2br(htmlspecialchars($menu_row['MENU_ID'])) . ')';
                    $htmlText_03 = "<td" .$BG_COLOR .">" .$COLUMN_03. "</td>";
                    $htmlText_04 = "<td" .$BG_COLOR .">" .$COLUMN_04. "</td>";
                  }
                }
                $str_temp =
<<< EOD
                    <tr valign="top">
                        <td class="likeHeader number" scope="row" >{$COLUMN_00}</td>
                        {$htmlText}
                        {$htmlText_02}
                        <td class="number" {$BG_COLOR}>{$COLUMN_02}</td>
                        {$htmlText_03}
                        {$htmlText_04}
                        <td{$BG_COLOR}>{$COLUMN_05}</td>
                        <td{$BG_COLOR}>{$COLUMN_06}</td>
                    </tr>
EOD;
                $output_str .= $str_temp;
                $temp_no++;
            }
            unset($objQuery);

            $str_temp =
<<< EOD
                </table>
                </div>
EOD;
            $output_str .= $str_temp;

            if( $num_rows < 1 ){
                $output_str = $g['objMTS']->getSomeMessage("ITAWDCH-MNU-1069056");
            }
        }
        else{
            throw new Exception( '00000400-([FILE]' . __FILE__ . ',[LINE]' . __LINE__ . ')' );
        }
    }
    catch (Exception $e){
        // エラーフラグをON
        $error_flag = 1;

        $tmpErrMsgBody = $e->getMessage();
        dev_log($tmpErrMsgBody, $intControlDebugLevel01);

        // DBアクセス事後処理
        if ( isset($objQuery) )    unset($objQuery);

        web_log($g['objMTS']->getSomeMessage("ITAWDCH-ERR-4011",array($strFxName,$tmpErrMsgBody)));
    }

    dev_log($g['objMTS']->getSomeMessage("ITAWDCH-STD-2",__FILE__),$intControlDebugLevel01);
?>
