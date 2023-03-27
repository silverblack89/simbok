<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Exportaccount;

/**
 * ExportaccountSearch represents the model behind the search form of `app\models\Exportaccount`.
 */
class ExportaccountSearch extends Exportaccount
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['no', 'program_id', 'premi_bpjs', 'premi_ketenagakerjaan', 'transportasi_akomodasi', 'hadiah', 'tenaga_kontrak', 'honorar_narsum', 'sewa_mobil_darat', 'sspd_luar_daerah', 'sspd_dalam_daerah', 'perangko_materai', 'jasa_transaksi_keuangan', 'penggandaan', 'cetak', 'atk', 'bahan_habis_pakai', 'makan_minum_kegiatan', 'jpk', 'bbm', 'internet_pulsa'], 'integer'],
            [['jumlah'], 'number'],
            [['period'], 'safe'],
            [['program'], 'string', 'max' => 255],
            [['username'], 'string', 'max' => 100],
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
    public function search($params)
    {
        $query = Exportaccount::find();

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
            'no' => $this->no,
            'premi_bpjs' => $this->premi_bpjs,
            'premi_ketenagakerjaan' => $this->premi_ketenagakerjaan,
            'transportasi_akomodasi' => $this->transportasi_akomodasi,
            'hadiah' => $this->hadiah,
            'tenaga_kontrak' => $this->tenaga_kontrak,
            'honorar_narsum' => $this->honorar_narsum,
            'sewa_mobil_darat' => $this->sewa_mobil_darat,
            'sspd_luar_daerah' => $this->sspd_luar_daerah,
            'sspd_dalam_daerah' => $this->sspd_dalam_daerah,
            'perangko_materai' => $this->perangko_materai,
            'jasa_transaksi_keuangan' => $this->jasa_transaksi_keuangan,
            'penggandaan' => $this->penggandaan,
            'cetak' => $this->cetak,
            'atk' => $this->atk,
            'bahan_habis_pakai' => $this->bahan_habis_pakai,
            'makan_minum_kegiatan' => $this->makan_minum_kegiatan,
            'jpk' => $this->jpk,
            'bbm' => $this->bbm,
            'internet_pulsa' => $this->internet_pulsa,
            'jumlah' => $this->jumlah,
        ]);

        $query->andFilterWhere(['like', 'program', $this->program]);

        return $dataProvider;
    }
}
