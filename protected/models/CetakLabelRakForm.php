<?php

/**
 * CetakLabelRakForm class.
 * CetakLabelRakForm is the data structure for keeping
 * Cetak Label Rak form data. It is used by the 'index' action of 'CetaklabelrakController'.
 * 
 * The followings are the available model relations:
 * @property Profil $profil
 */
class CetakLabelRakForm extends CFormModel
{

    public $profilId;
    public $rakId;
    public $dari;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('profilId, rakId, dari', 'safe')
        );
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array(
            'profilId' => 'Profil Supplier',
            'rakId' => 'User',
            'dari' => 'Harga jual berubah dari',
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'profil' => array(self::BELONGS_TO, 'Profil', 'profilId'),
            'rak' => array(self::BELONGS_TO, 'Rak', 'rakId'),
        );
    }

    public function getNamaProfil()
    {
        $profil = Profil::model()->findByPk($this->profilId);
        return $profil->nama;
    }

    public function getNamaRak()
    {
        $rak = RakBarang::model()->findByPk($this->rakId);
        return $rak->nama;
    }

    public function inputBarangKeCetak()
    {
        if (!empty($this->profilId || !empty($this->rakId) || !empty($this->dari))) {
            $sqlProfil = '';
            $sqlRak = '';
            $sqlDari = '';

            if (!empty($this->profilId)) {
                $sqlProfil = "JOIN supplier_barang sp ON barang.id = sp.barang_id AND sp.supplier_id = :supplierId";
            }
            if (!empty($this->rakId)) {
                $sqlRak = "WHERE rak_id = :rakId";
            }
            if (!empty($this->dari)) {
                $sqlDari = "JOIN
                        barang_harga_jual bhj ON barang.id = bhj.barang_id
                            AND DATE_FORMAT(bhj.updated_at, '%Y-%m-%d %H:%i') >= :dari";
            }

            /* Menambahkan barang yang belum ada di tabel label_rak_cetak */
            $tabelCetak = LabelRakCetak::model()->tableName();
            $sql = "INSERT IGNORE INTO {$tabelCetak} (barang_id)
                SELECT 
                    barang.id
                FROM
                    barang
                    {$sqlDari}
                    {$sqlProfil}
                {$sqlRak}";
            $command = Yii::app()->db->createCommand($sql);


            if (!empty($this->profilId)) {
                $command->bindValue(':supplierId', $this->profilId);
            }
            if (!empty($this->rakId)) {
                $command->bindValue(':rakId', $this->rakId);
            }
            if (!empty($this->dari)) {
                $command->bindValue(':dari', $this->dari);
            }

            return $command->execute();
        }
    }

}