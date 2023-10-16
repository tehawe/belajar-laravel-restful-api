<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\User;
use App\Http\Requests\ContactCreateRequest;
use App\Http\Requests\ContactUpdateRequest;
use App\Http\Resources\ContactCollection;
use App\Http\Resources\ContactResource;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function create(ContactCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();

        $contact = new Contact($data);
        $contact->user_id = $user->id;
        $contact->save();

        return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    public function getContact(User $user, int $contact_id): Contact
    {
        $contact = Contact::where('id', $contact_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$contact) {
            throw new HttpResponseException(
                response()
                    ->json([
                        'errors' => [
                            'message' => ['contact not found'],
                        ],
                    ])
                    ->setStatusCode(404),
            );
        }
        return $contact;
    }

    public function get(int $id): ContactResource
    {
        $user = Auth::user();
        $contact = $this->getContact($user, $id);

        return new ContactResource($contact);
    }

    public function update(int $id, ContactUpdateRequest $request): ContactResource
    {
        $user = Auth::user();

        $contact = $this->getContact($user, $id);

        $data = $request->validated();

        $contact->fill($data);
        $contact->save();

        return new ContactResource($contact);
    }

    public function delete(int $id): JsonResponse
    {
        $user = Auth::user();

        $contact = $this->getContact($user, $id);

        $contact->delete();

        return response()
            ->json([
                'data' => true,
                'action' => ['delete' => 'success'],
            ])
            ->setStatusCode(200);
    }

    public function search(Request $request): ContactCollection
    {
        // Cek login user
        $user = Auth::user();

        // Config paginate
        $page = $request->input('page', 1);
        $size = $request->input('size', 10);

        // Query Search
        $contacts = Contact::query()->where('user_id', $user->id);

        $contacts = $contacts->where(function (Builder $builder) use ($request) {
            // Search by name (first_name / last_name)
            $name = $request->input('name');
            if ($name) {
                $builder->where(function (Builder $builder) use ($name) {
                    $builder->orWhere('first_name', 'like', '%' . $name . '%');
                    $builder->orWhere('last_name', 'like', '%' . $name . '%');
                });
            }

            // Search by email
            $email = $request->input('email');
            if ($email) {
                $builder->where('email', 'like', '%' . $email . '%');
            }

            // Search by phone
            $phone = $request->input('phone');
            if ($phone) {
                $builder->where('phone', 'like', '%' . $phone . '%');
            }
        });

        // Display paginate
        $contacts = $contacts->paginate(perPage: $size, page: $page);

        return new ContactCollection($contacts);
    }
}
