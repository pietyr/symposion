<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AccountStateFilter;
use App\Enums\Profession;
use App\Enums\RegistrationStatus;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string|null $academic_title shown before the name, e.g. "dr n. med."
 * @property string $first_name
 * @property string $last_name
 * @property-read string $name the first and last name together
 * @property string $email
 * @property string|null $profession a Profession value, kept as text (ADR-017)
 * @property string|null $license_number number of the right to practise (PWZ)
 * @property string|null $university students only
 * @property string|null $student_number students only
 * @property string|null $company medical representatives only
 * @property string|null $profession_name the profession of someone who chose "other"
 * @property UserRole $role
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Specialization> $specializations
 * @property-read Collection<int, Registration> $registrations
 */
#[Fillable(['academic_title', 'first_name', 'last_name', 'email', 'password', 'profession', 'license_number', 'university', 'student_number', 'company', 'profession_name'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
final class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Role is deliberately not mass assignable; it is set explicitly.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => 'user',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * @return Attribute<string, never>
     */
    protected function name(): Attribute
    {
        return Attribute::get(fn (): string => trim($this->first_name.' '.$this->last_name));
    }

    /**
     * Specializations a doctor holds.
     *
     * @return BelongsToMany<Specialization, $this>
     */
    public function specializations(): BelongsToMany
    {
        return $this->belongsToMany(Specialization::class);
    }

    /**
     * @return HasMany<Registration, $this>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class)->chaperone();
    }

    /**
     * Match every word of the term in the first name, the last name or the email address. The collation already
     * ignores case and most Polish diacritics, but treats "ł" as a separate letter, so it is folded here.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function matchingSearch(Builder $query, string $term): void
    {
        foreach (Str::of($term)->squish()->explode(' ')->filter() as $word) {
            $pattern = '%'.addcslashes(str_replace(['ł', 'Ł'], 'l', $word), '%_\\').'%';

            $query->where(fn (Builder $wordQuery) => $wordQuery
                ->whereRaw("REPLACE(REPLACE(CONCAT(first_name, ' ', last_name), 'ł', 'l'), 'Ł', 'l') LIKE ?", [$pattern])
                ->orWhere('email', 'like', $pattern));
        }
    }

    /**
     * Narrow the admin user list down by whichever filters are set.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function filteredBy(
        Builder $query,
        ?Profession $profession = null,
        ?AccountStateFilter $state = null,
        string $search = '',
    ): void {
        $query
            ->when($profession, fn (Builder $query, Profession $profession) => $query->where('profession', $profession->value))
            ->when($state, fn (Builder $query, AccountStateFilter $state) => match ($state) {
                AccountStateFilter::Verified => $query->whereNotNull('email_verified_at'),
                AccountStateFilter::Unverified => $query->whereNull('email_verified_at'),
            })
            ->when($search !== '', fn (Builder $query) => $query->matchingSearch($search));
    }

    /**
     * Count the registrations that take a seat (ADR-019) into registrations_count.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function withRegistrationsCount(Builder $query): void
    {
        $query->withCount([
            'registrations' => fn (Builder $registrations) => $registrations->whereIn('status', RegistrationStatus::seatTaking()),
        ]);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }
}
