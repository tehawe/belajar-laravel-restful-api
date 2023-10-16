<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressCreateRequest;
use App\Http\Requests\AddressUpdateRequest;
use App\Http\Resources\AddressResource;
use App\Http\Resources\ContactResource;
use App\Models\Address;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    private function getContact(User $user, int $contact_id): Contact
    {
        $contact = Contact::where('user_id', $user->id)
            ->where('id', $contact_id)
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

    private function getAddress(Contact $contact, int $address_id): Address
    {
        $address = Address::where('contact_id', $contact->id)
            ->where('id', $address_id)
            ->first();
        if (!$address) {
            throw new HttpResponseException(
                response()
                    ->json([
                        'errors' => [
                            'message' => ['data not found'],
                        ],
                    ])
                    ->setStatusCode(404),
            );
        }
        return $address;
    }

    public function create(int $contact_id, AddressCreateRequest $request): JsonResponse
    {
        $user = Auth::user();
        $contact = $this->getContact($user, $contact_id);

        $data = $request->validated();
        $address = new Address($data);
        $address->contact_id = $contact->id;
        $address->save();

        return (new AddressResource($address))->response()->setStatusCode(201);
    }

    public function get(int $contact_id, int $address_id): AddressResource
    {
        $user = Auth::user();
        $contact = $this->getContact($user, $contact_id);
        $address = $this->getAddress($contact, $address_id);

        return new AddressResource($address);
    }

    public function update(int $contact_id, int $address_id, AddressUpdateRequest $request): AddressResource
    {
        $user = Auth::user();
        $contact = $this->getContact($user, $contact_id);
        $address = $this->getAddress($contact, $address_id);

        $data = $request->validated();
        $address->fill($data);
        $address->save();

        return new AddressResource($address);
    }

    public function delete(int $contact_id, int $address_id): JsonResponse
    {
        $user = Auth::user();
        $contact = $this->getContact($user, $contact_id);
        $address = $this->getAddress($contact, $address_id);

        $address->delete();

        return response()->json([
            'data' => true,
            'action' => ['delete' => 'success'],
        ])->setStatusCode(200);
    }

    public function list(int $contact_id): JsonResponse
    {
        $user = Auth::user();
        $contact = $this->getContact($user, $contact_id);

        $address = Address::where('contact_id', $contact->id)->get();

        return (AddressResource::collection($address))->response()->setStatusCode(200);
    }
}
