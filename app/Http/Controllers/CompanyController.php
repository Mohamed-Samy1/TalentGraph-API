<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponses;

use App\Http\Requests\CompanyStoreRequest;
use App\Http\Requests\CompanyUpdateRequest;

use App\Http\Resources\CompanyResource;

use App\Policies\CompanyPolicy;

use App\Models\Company;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    use ApiResponses;

    public function index()
    {
        try {
            
            $companies = Company::with('employer')->get();
        
            return $this->ok('Companies fetched successfully', [
                'companies' => CompanyResource::collection($companies)
            ]);

        } catch (\Throwable $e) {
            return $this->error('Companies did not fetched', 500);
        }
    }

    public function store(CompanyStoreRequest $request)
    {
        try {
            $user = auth()->user();
            $policy = app(CompanyPolicy::class);
            $canCreate = $policy->store($user);

            if (!$canCreate) {
                return $this->error('You are not authorized to create companies', 403);
            }

            if ($user->company) {
                return $this->error('You already have a company. Each employer can only create one company.', 422);
            }

            $attributes = $request->mappedAttributes();
            $attributes['employer_id'] = $user->id;
            
            $company = Company::create($attributes);
            
            return $this->created('Company created successfully', [
                'company' => new CompanyResource($company)
            ]);

        } catch (\Throwable $e) {
            return $this->error('Could not add the company to the system', 500);
        }
    }

    public function show(Company $company)
    {
        try {
            $user = auth()->user();
            $policy = app(CompanyPolicy::class);
            $canView = $policy->view($user, $company);

            if (!$canView) {
                return $this->error('You are not authorized to view this company', 403);
            }

            $company->load('employer', 'vacancies');

            return $this->ok('Company fetched successfully', [
                'company' => new CompanyResource($company)
            ]);

        } catch (\Throwable $e) {
            return $this->error('Could not view company details', 500);
        }
    }

    public function update(CompanyUpdateRequest $request, Company $company)
    {
        try {

            $user = auth()->user();
            $policy = app(CompanyPolicy::class);
            $canUpdate = $policy->update($user, $company);

            if (!$canUpdate) {
                return $this->error('You are not authorized to update this company', 403);
            }

            $attributes = $request->mappedAttributes();
            $company->update($attributes);
            
            return $this->ok('Company updated successfully', [
                'company' => new CompanyResource($company->fresh())
            ]);

        } catch (\Throwable $e) {
            return $this->error('Could not update the company details', 500);
        }
    }

    public function destroy(Company $company)
    {
        try {
            $user = auth()->user();
            $policy = app(CompanyPolicy::class);
            $canDelete = $policy->delete($user, $company);

            if (!$canDelete) {
                return $this->error('You are not authorized to delete this company', 403);
            }

            $company->delete();
            
            return $this->ok('Company deleted successfully');

        } catch (\Throwable $e) {
            return $this->error('Could not delete the company', 500);
        }
    }
}