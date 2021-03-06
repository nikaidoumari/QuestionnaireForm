<?php
class Controller_Questionnaire extends Controller
{
	public function action_index()
	{
		//POSTが来たとき->アンケートフォームを送る
		if( empty( Input::post() ) ){
			//フォームテンプレートに入れるアンケート
			$questions = "";
			//データベースに接続して、配列に代入
			$query = DB::select()->from("qes")->execute()->as_array();
			//var_dump($query);
			$count = count($query); //for文の外で宣言すると早くなるらしい。
			for ( $i = 0; $i < $count; $i++ ){
				// var_dump( $query[$i] );
				//選択式質問の追加
				if( $query[$i]["qType"] === "radio" ){
					//csv形式の選択肢を配列に変換(リレーショナルDBはクソ)
					$qChoice = explode( ",", $query[$i]["qChoice"] );
					$questions .= View::forge("questionnaire/qpartsradio",array(
						"labelid" => "qes_".$query[$i]["id"],
						"qflabel" => $query[$i]["qText"],
						"anslist" => $qChoice,
					));
				}
				//回答形式の問題の追加
				else if( $query[$i]["qType"] === "textarea" ){
					$questions .= View::forge("questionnaire/qpartstextarea",array(
						"labelid" => "qes_".$query[$i]["id"],
						"qflabel" => $query[$i]["qText"],
					));
				}
			}
			return Response::forge(View::forge('questionnaire/qform',array("questio" => $questions,),false));
		}
		else{
			$postdata = Input::post();
			$body = "";
			// var_dump($postdata);
			//データベースに接続して、配列に代入
			$query = DB::select()->from("qes")->execute()->as_array();
			//var_dump($query);
			$count = count($query); //for文の外で宣言すると早くなるらしい。
			//データベースに登録
			try {
				for ( $i = 0; $i < $count; $i++ ){
					// echo $query[$i]["qText"].":::";
					// echo $postdata["qes_".$query[$i]["id"]].":::";
					// echo $query[$i]["id"];
					$body .= View::forge("questionnaire/ansresult",array(
						"labelid" => "qes_".$query[$i]["id"],
						"qflabel" => $query[$i]["qText"],
						"ddd" => $postdata["qes_".$query[$i]["id"]],
					),false);

					$result = DB::insert("ans")->set(array(
						"ansid" => $query[$i]["id"],
						"aText" => $postdata["qes_".$query[$i]["id"]],
					))->execute();
				}
			} catch (Exception $e) {
				echo("mysql erorr");
				return false;
			}

			return Response::forge(View::forge('questionnaire/postend.php',array("questio" => $body,),false));
		}
	}

	public function action_views()
	{
		$tablebody = "";
		$query = DB::query('SELECT qes.qText,ans.aText FROM ans INNER JOIN qes ON ans.ansid = qes.id ORDER BY qes.id')->execute()->as_array();
		//var_dump($query);
		$count = count($query); //for文の外で宣言すると早くなるらしい。
		//echo $count;
		for ( $i = 0; $i < $count; $i++ ){
			$tablebody.="<tr>";
			$tablebody.="<td>".$query[$i]["qText"]."</td>";
			$tablebody.="<td>".$query[$i]["aText"]."</td>";
			$tablebody.="</tr>";
		}
		//echo $tablebody;
		return Response::forge(View::forge('questionnaire/table',array("tablebody" => $tablebody,),false));
	}

	public function action_404()
	{
		echo "404 Not found.";
	}
	public function action_defaultpage()
	{
		return Response::forge(View::forge('questionnaire/index'));
	}
}
