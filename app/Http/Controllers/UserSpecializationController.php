<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssignmentUserSpecialization;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserSpecialization;
use Exception;

class UserSpecializationController extends Controller
{
    public function list()
    {
        $userSpecializations = UserSpecialization::all();
        $doctor = User::where('user_level', 3)->where('status', '=',1)->get();
        $specializations = Specialization::all()->where('quantity_voucher', '>', 0);

        return view('admin.assignment.assignment', compact('doctor', 'specializations', 'userSpecializations'));
    }

    public function insert(StoreAssignmentUserSpecialization $request)
    {
        // Obtener los datos de la solicitud
        $doctorId = $request->input('id_doctor');
        $specializationId = $request->input('id_specialization');
        $voucher = $request->input('vaucher_specialization');
        
        try {
            DB::beginTransaction();

            // Obtener la especialización
            $specialization = Specialization::findOrFail($specializationId);
            
            // Obtener la suma total de cupos ya asignados para esta especialización
            $cuposAsignadosTotales = UserSpecialization::where('id_specialization', $specializationId)
                ->sum('cupo_doctor');
            
            // Verificar si ya existe una asignación entre usuario y especialización
            $existingAssignment = UserSpecialization::where('id_user', $doctorId)
                ->where('id_specialization', $specializationId)
                ->first();

            if ($existingAssignment) {
                // CASO 1: Ya existe la asignación - ACTUALIZAR cupo
                
                // Calcular cupos sin contar el cupo actual del doctor
                $cuposSinEsteDoctor = $cuposAsignadosTotales - $existingAssignment->cupo_doctor;
                
                // Verificar si el nuevo cupo excede el límite
                if (($cuposSinEsteDoctor + $voucher) > $specialization->quantity_voucher) {
                    $cuposDisponibles = $specialization->quantity_voucher - $cuposSinEsteDoctor;
                    DB::rollBack();
                    return redirect('admin/assignment')->with('error', 'No se puede asignar ' . $voucher . ' cupos. Máximo disponible para asignar: ' . $cuposDisponibles . ' cupos. (Total especialidad: ' . $specialization->quantity_voucher . ', Ya asignados a otros: ' . $cuposSinEsteDoctor . ')');
                }
                
                // Actualizar el cupo del doctor
                $existingAssignment->cupo_doctor = $voucher;
                $existingAssignment->save();
                
                DB::commit();
                return redirect('admin/assignment')->with('success', 'Asignación actualizada con éxito. Nuevo cupo: ' . $voucher);
            }

            // CASO 2: Nueva asignación
            
            // Verificar si la suma de cupos excede el total de la especialidad
            if (($cuposAsignadosTotales + $voucher) > $specialization->quantity_voucher) {
                $cuposDisponibles = $specialization->quantity_voucher - $cuposAsignadosTotales;
                
                if ($cuposDisponibles <= 0) {
                    DB::rollBack();
                    return redirect('admin/assignment')->with('error', 'Los cupos de esta especialidad están completos. Total: ' . $specialization->quantity_voucher . ', Ya asignados: ' . $cuposAsignadosTotales);
                }
                
                DB::rollBack();
                return redirect('admin/assignment')->with('error', 'No se puede asignar ' . $voucher . ' cupos. Máximo disponible para asignar: ' . $cuposDisponibles . ' cupos. (Total especialidad: ' . $specialization->quantity_voucher . ', Ya asignados: ' . $cuposAsignadosTotales . ')');
            }

            // Crear nueva asignación
            $userSpecialization = new UserSpecialization;
            $userSpecialization->id_user = $doctorId;
            $userSpecialization->id_specialization = $specializationId;
            $userSpecialization->cupo_doctor = $voucher;
            $userSpecialization->save();

            DB::commit();
            return redirect('admin/assignment')->with('success', 'Asignación registrada con éxito. Cupos asignados: ' . $voucher . '. Cupos restantes de la especialidad: ' . ($specialization->quantity_voucher - $cuposAsignadosTotales - $voucher));
            
        } catch (Exception $e) {
            DB::rollBack();
            return redirect('admin/assignment')->with('error', 'Error al registrar la asignación: ' . $e->getMessage());
        }
    }


    public function delete($id)
    {
        // Buscar la asignación por su ID
        $userSpecialization = UserSpecialization::find($id);

        // Eliminar la asignación (no tocar quantity_voucher de la especialización)
        $userSpecialization->delete();
        
        return redirect('admin/assignment')->with('success', 'La asignación fue eliminada');
    }
}
