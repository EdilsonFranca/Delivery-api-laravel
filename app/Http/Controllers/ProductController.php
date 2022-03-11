<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{

    public function index(): \Illuminate\Http\JsonResponse
    {
        try {
            return response()->json(array(
                'success' => true,
                'data'    => Product::all(),
            ));

        } catch (\Exception $e) {
            return response()->json(array(
                'success'      => false,
                'message'      => 'Houve um erro',
                'messageError' => $e->getMessage(),
            ));
        }
    }

    public function create(Request $request): \Illuminate\Http\JsonResponse
    {
        $data       = $request->only(['name', 'price','price_promotion', 'description', 'category_id', 'photo']);
        $validation = $this->getValidate($data);

        if ($validation->fails()) {
            return response()->json(array(
                    'success' => false,
                    'message' => $validation->errors()
            ));
        }

        $product = Product::where('name', $data['name'])
                            ->where('price', $data['price'])
                            ->where('description', $data['description'])
                            ->first();

        if (is_null($product)) {
            $product = Product::create($data);
        }

        return response()->json(array(
                'success' => true,
                'data'    => $product,
                'message' => 'product  cadastrados com sucesso !'
        ));

    }

    /**
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function getValidate(array $data): \Illuminate\Contracts\Validation\Validator
    {
        $messages = array(
               'required' => 'o campo :attribute  não pode ser vazio !'
        );

        return Validator::make($data, array(
            'name'        => 'required',
            'price'       => 'required',
            'description' => 'required',
        ), $messages);
    }

    public function store(Request $request){}

    public function show(int $id): \Illuminate\Http\JsonResponse {
        try {
            return response()->json(array(
                'success' => true,
                'data'    => Product::findOrFail($id),
            ));
        }
        catch (\Exception $e) {
            return response()->json(array(
                'success'      => false,
                'message'      => 'Houve um erro',
                'messageError' => $e->getMessage(),
            ));
        }
    }

    public function edit(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            return response()->json(array(
                'success' => true,
                'data'    => Product::findOrFail($id),
            ));

        } catch (\Exception $e) {
            return response()->json(array(
                'success' => false,
                'message' => 'Houve um erro',
                'messageError' => $e->getMessage(),
            ));
        }
    }

    public function update(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $data    = $request->only(['name', 'price','price_promotion', 'description', 'category_id', 'photo']);
        $product = Product::find($id);

        if (is_null($product)) {
            return response()->json([ 'message' => 'product não encontrado'], 404);
        }

        $product_fill = array(
            'name'            => $data['name'],
            'price'           => $data['price'],
            'price_promotion' => $data['price_promotion'],
            'description'     => $data['description'],
        );

        $product->fill($product_fill);

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $name = str_slug($data['name']) . '_' . time();
            $path = $request->photo->storeAs('images', $name . '.' . $request->photo->getClientOriginalExtension());
            $product->photo = $path;
            $product->photo = $product->getCapaUrlAtribute();
        }

        $product->save();

        return response()->json(array(
            'success' => true,
            'data'    => $product,
            'message' => 'product  atualizado com sucesso !'
        ));
    }

    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        $qtd = Product::destroy($id);

        if ($qtd === 0) {
            return response()->json(['erro' => 'product não encontrado'], 404);
        }

        return response()->json(array(
            'success' => true,
            'message' => 'product  removido com sucesso !'
        ));
    }
}
