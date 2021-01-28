<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'products';
    protected $appends = ['photo_url'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'consignment_fee_persent', 'consignment_fee_rupiah', 'is_active', 'product_name', 'category_product_id', 'price', 'stock', 'minimum_order', 'product_description', 'weight', 'unit', 'condition', 'preorder', 'grosir', 'gambar', 'created_at',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'updated_at', 'detail',
    ];

    public function getPhotoUrlAttribute()
    {
        // if (!isset($this->gambar)){
        //     $this->photo = 'default.jpg';
        // }
        $image_url = 'https://staging-merchant.dompetaman.com/assets/product-image/' . $this->gambar;
        return $image_url;
    }

    // public function ProductImages()
    // {
    //     return $this->hasMany("App\Models\ProductImage");
    // }
}
