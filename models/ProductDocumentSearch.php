<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\ProductDocument;
use yii\data\SqlDataProvider;
use yii\helpers\VarDumper;

/**
 * ProductDocumentSearch represents the model behind the search form of `app\models\ProductDocument`.
 */
class ProductDocumentSearch extends ProductDocument
{
    public $per_page;
    public $page;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'doc_type', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['doc_number'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params,$type=null)
    {
        $query = ProductDocument::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'doc_type' => $type,
        ]);

        $query->andFilterWhere(['like', 'doc_number', $this->doc_number])
            ->andFilterWhere(['!=', 'status', BaseModel::STATUS_DELETE]);

        return $dataProvider;
    }

    /**
     * Search
     * $param array $params
     *
     * @return ActiveDataProvider
     * */
    public function searchReport($params, $type)
    {
        if($params['ProductDocument'] && $type){
            $product = $params['ProductDocument'];
            $start_date = $product['start_date'];
            $end_date = $product['end_date'];
            $product_id = $product['product_id'];
            $party_number = $product['party_number'];
            $sql = "
                SELECT p.name as name, p.partiy_number as pnumber, pd.doc_number as doc_number, pd.date as date, pib.amount as amount, pib.quantity as quantity,
                pd.doc_type as type from product p
                inner join product_document_items pdi on pdi.product_id = p.id
                inner join product_items_balance pib on pib.product_doc_items_id = pdi.id
                inner join product_document pd on pd.id = pdi.product_doc_id
                WHERE pd.doc_type = {$type}            
            ";
            if(!empty($start_date))
            {
                $start_date = date('Y-m-d', strtotime($start_date));
                $sql .= " AND pd.date >= '{$start_date}'";
            }
            if(!empty($end_date))
            {
                $end_date = date('Y-m-d', strtotime($end_date));
                $sql .= " AND pd.date <= '{$end_date}'";
            }
            if(!empty($product_id))
                $sql .= " AND pib.product_id = $product_id";
            if(!empty($party_number))
                $sql .= " AND p.partiy_number = {$party_number}";
            $sql .= " ORDER BY pib.id ASC";
            $query = \Yii::$app->db->createCommand($sql)->queryAll();
            $count = count($query);
            $dataProvider = new SqlDataProvider([
                'sql' => $sql,
                'totalCount' => $count,
            ]);


            if(!empty($dataProvider))
                return $dataProvider;

            return false;
        }
    }
}
