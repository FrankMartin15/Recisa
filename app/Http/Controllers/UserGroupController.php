<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserGroup;
use App\Models\User;
use App\Models\UserGroup;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserGroupController extends Controller
{

    

    public function list()
    {
        //all recuperar todos nuestros registros
        $roles=UserGroup::orderBy('group_level','asc')->get();
        $admin=User::where('user_level',1)->count();
        $secretary=User::where('user_level',2)->count();
        $doctor=User::where('user_level',3)->count();
        
        // Obtener usuarios por nivel para el tooltip
        $adminUsers = User::where('user_level', 1)->get(['names', 'surnames']);
        $secretaryUsers = User::where('user_level', 2)->get(['names', 'surnames']);
        $doctorUsers = User::where('user_level', 3)->get(['names', 'surnames']);
        
        return view('admin.rol.list',compact('roles','admin','secretary','doctor','adminUsers','secretaryUsers','doctorUsers'));
    }
    public function add(){
        return view('admin.rol.created');
    }
    public function insert(Request $request){
        // 1. Validación inicial (ahora sobre el campo 'slug')
        $request->validate([
            'slug' => 'required|string|max:255|unique:user_groups,slug',
            'group_status' => 'required|boolean'
        ], [
            'slug.required' => 'El nombre del rol es obligatorio.',
            'slug.unique' => 'Ya existe un rol con este nombre.',
            'group_status.required' => 'Debe seleccionar un estado.',
        ]);

        $slugInput = trim($request->input('slug'));
        $groupLevel = 0;

        // 2. Lógica para asignar Nivel basado en el Nombre (slug)
        switch (strtolower($slugInput)) {
            case 'admin':
                $groupLevel = 1;
                break;
            case 'secretaria':
                $groupLevel = 2;
                break;
            case 'doctor':
                $groupLevel = 3;
                break;
            default:
                // Para cualquier otro rol, calcula el siguiente nivel disponible
                $highestLevel = UserGroup::max('group_level');
                $groupLevel = ($highestLevel >= 3) ? $highestLevel + 1 : 4;
                break;
        }

        // 3. Validación adicional para niveles fijos
        if (in_array($groupLevel, [1, 2, 3])) {
            $existingRole = UserGroup::where('group_level', $groupLevel)->first();
            if ($existingRole) {
                return back()->withInput()->withErrors(['slug' => "El rol principal para el nivel {$groupLevel} ('{$existingRole->slug}') ya existe."]);
            }
        }
        
        // 4. Guardar en la base de datos
        try {
            DB::beginTransaction();
            
            $usergroup = new UserGroup();
            $usergroup->group_level = $groupLevel; // Asignamos el nivel calculado
            $usergroup->group_status = $request->input('group_status');
            $usergroup->slug = $slugInput; // Usamos el slug que vino del formulario
            $usergroup->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            // Para depurar, es útil ver el error: return back()->with('error', 'Error: ' . $e->getMessage());
            return redirect('admin/rol/list')->with('error', 'Ocurrió un error al registrar el rol.'); 
        }

        return redirect('admin/rol/list')->with('success', '¡Rol registrado exitosamente!'); 
    }

    public function edit(UserGroup $usergroup){
        if(!empty($usergroup)){
            //Si carga la vista con los datos
            return view('admin.rol.edit',compact('usergroup'));
        }else{
            //Error 404
            return view('page.404');
        }
    }
    public function update(Request $request, UserGroup $usergroup)
{
    // 1. Validación
    request()->validate([
        'slug' => 'required|string|max:255|unique:user_groups,slug,' . $usergroup->id,
        'group_status' => 'required|boolean',
        'group_level' => 'required|integer' // Validamos que el nivel llegue
    ]);

    // 2. Lógica de actualización
    // Solo actualizamos el nombre (slug) y el estado. El nivel no se toca.
    // Excepto si el nivel es 1, 2 o 3, en cuyo caso no permitimos cambiar el slug.
    if (!in_array($usergroup->group_level, [1, 2, 3])) {
        $usergroup->slug = $request->slug;
    }
    
    $usergroup->group_status = $request->group_status;
    $usergroup->save();
    
    return redirect('admin/rol/list')->with('success', 'El rol ' . $usergroup->slug . ' fue actualizado');
}

    public function delete($id){
        $usergroup=UserGroup::find($id);
        $usergroup->delete();
        return redirect('admin/rol/list')->with('success','El rol fue eliminado'); 
    }    

}
