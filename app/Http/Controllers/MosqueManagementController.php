<?php

namespace App\Http\Controllers;

use App\Models\Mosque;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MosqueManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mosques = Mosque::with('creator')->latest()->paginate(20);
        return view('admin.mosques.index', compact('mosques'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.mosques.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       

        // Validate the request data
         // Custom validation messages
         $messages = [
            'name.required' => 'اسم المسجد مطلوب',
            'address.required' => 'العنوان مطلوب',
            'city.required' => 'المدينة مطلوبة',
            'image_path.image' => 'يجب أن يكون الملف صورة',
            'image_path.max' => 'حجم الصورة يجب أن لا يتجاوز 2 ميجابايت',
            'latitude.numeric' => ' صالح بين -90 و 90 خط العرض يجب أن يكون رقماً',
            'longitude.numeric' => 'صالح بين -180 و 180 خط الطول يجب أن يكون رقماً',
        ];

         // Validate the request data

        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'image_path' => 'nullable|image|max:2048',
            'is_active' => 'nullable|boolean',
        ], [], $messages);
        

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator, 'mosque')
                ->withInput();
        }
    

        $data = $validator->validated();

        // Handle image upload
        if ($request->hasFile('image_path')) {
            $data['image_path'] = $request->file('image_path')->store('mosques', 'public');
        }

        // Set created_by to current user
        $data['created_by'] = auth()->id();

        Mosque::create($data);

        return redirect()->route('mosques.index')
            ->with('success', 'تم إضافة المسجد بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Mosque $mosque)
    {
        $mosque->load('creator', 'courses.sheikh');
        return view('admin.mosques.show', compact('mosque'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mosque $mosque)
    {
        return view('admin.mosques.edit', compact('mosque'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mosque $mosque)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'image_path' => 'nullable|image|max:2048',
            'is_active' => 'nullable|boolean',
        ];
        
        $messages = [
            'name.required' => 'اسم المسجد مطلوب',
            'address.required' => 'العنوان مطلوب',
            'city.required' => 'المدينة مطلوبة',
            'image_path.image' => 'يجب أن يكون الملف صورة',
            'image_path.max' => 'حجم الصورة يجب أن لا يتجاوز 2 ميجابايت',
            'latitude.numeric' => 'خط العرض يجب أن يكون رقماً صالحاً بين -90 و 90',
            'longitude.numeric' => 'خط الطول يجب أن يكون رقماً صالحاً بين -180 و 180',
        ];


        $validator = Validator::make($request->all(), $rules, $messages);
        

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator, 'mosque')
                ->withInput();
        }

        $data = $validator->validated();

        // Handle image update
        if ($request->hasFile('image_path')) {
            // Delete old image if exists
            if ($mosque->image_path) {
                Storage::disk('public')->delete($mosque->image_path);
            }
            $data['image_path'] = $request->file('image_path')->store('mosques', 'public');
        }

        $mosque->update($data);

        return redirect()->route('mosques.index')
            ->with('success', 'تم تحديث بيانات المسجد بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mosque $mosque)
    {
        // Delete associated image
        if ($mosque->image_path) {
            Storage::disk('public')->delete($mosque->image_path);
        }

        $mosque->delete();

        return redirect()->route('mosques.index')
            ->with('success', 'تم حذف المسجد بنجاح');
    }
    
    /**
     * Toggle mosque active status
     */
    public function toggleStatus(Mosque $mosque)
    {
        $mosque->update(['is_active' => !$mosque->is_active]);
        
        $status = $mosque->is_active ? 'تم تفعيل المسجد' : 'تم تعطيل المسجد';
        return back()->with('success', $status);
    }
}