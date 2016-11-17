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
					//csv形式の選択肢を配列に変換(リレーショナルDBはクソ))
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
			while ($key = current($postdata)){
				var_dump( $key);
			}
			// for( $i = 0; $i < $count; $i++){
				//var_dump( $postdata );
			// }
		}
	}
	public function action_404()
	{
		echo "404 Not found.";
	}
}