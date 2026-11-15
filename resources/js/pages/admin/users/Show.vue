<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import RegistrationController from '@/actions/App/Http/Controllers/Admin/RegistrationController';
import BlockUserController from '@/actions/App/Http/Controllers/Admin/BlockUserController';
import RestoreUserController from '@/actions/App/Http/Controllers/Admin/RestoreUserController';
import UnblockUserController from '@/actions/App/Http/Controllers/Admin/UnblockUserController';
import UserController from '@/actions/App/Http/Controllers/Admin/UserController';
import VerifyUserController from '@/actions/App/Http/Controllers/Admin/VerifyUserController';
import ActionDialog from '@/components/ActionDialog.vue';
import AccountDetails from '@/components/admin/users/AccountDetails.vue';
import ProfileDetails from '@/components/admin/users/ProfileDetails.vue';
import Heading from '@/components/Heading.vue';
import PaginationControls from '@/components/PaginationControls.vue';
import RegistrationStatusBadge from '@/components/registrations/RegistrationStatusBadge.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { usePriceFormat } from '@/composables/usePriceFormat';
import { index } from '@/routes/admin/users';
import type {
    Paginated,
    ParticipantResource,
    RegistrationResource,
} from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Users',
                href: index(),
            },
            {
                title: 'User details',
            },
        ],
    },
});

const props = defineProps<{
    account: ParticipantResource;
    registrations: Paginated<RegistrationResource>;
}>();

const { formatPrice } = usePriceFormat();
const dateFormatter = new Intl.DateTimeFormat('pl', {
    dateStyle: 'short',
    timeStyle: 'short',
    timeZone: 'Europe/Warsaw',
});

const titledName = computed(() =>
    [props.account.academic_title, props.account.name]
        .filter(Boolean)
        .join(' '),
);
</script>

<template>
    <Head :title="$t('User details')" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex flex-wrap items-center gap-3">
            <Heading :title="titledName" />
            <Badge v-if="account.is_admin" variant="secondary" class="mb-8">
                {{ $t('Administrator') }}
            </Badge>
            <Badge
                v-if="!account.email_verified_at"
                variant="outline"
                class="mb-8"
                data-test="account-unverified"
            >
                {{ $t('Unverified') }}
            </Badge>
            <Badge
                v-if="account.deleted_at"
                variant="secondary"
                class="mb-8"
                data-test="account-deleted"
            >
                {{ $t('Account deleted') }}
            </Badge>
            <Badge
                v-if="account.blocked_at"
                variant="destructive"
                class="mb-8"
                data-test="account-blocked"
            >
                {{ $t('Blocked') }}
            </Badge>
            <div
                v-if="!account.is_admin && !account.deleted_at"
                class="mb-8 flex flex-wrap gap-2 sm:ml-auto"
            >
                <ActionDialog
                    v-if="!account.email_verified_at"
                    :action="VerifyUserController.form(account.id)"
                    :label="$t('Verify account')"
                    :title="
                        $t('Verify the account of :name?', {
                            name: account.name,
                        })
                    "
                    :description="
                        $t('The email address will be marked as verified.')
                    "
                    test-id="verify-account"
                />
                <Button variant="outline" as-child>
                    <Link
                        :href="UserController.edit(account.id)"
                        data-test="edit-user-link"
                    >
                        {{ $t('Edit') }}
                    </Link>
                </Button>
                <ActionDialog
                    v-if="account.blocked_at"
                    :action="UnblockUserController.form(account.id)"
                    :label="$t('Unblock')"
                    :title="
                        $t('Unblock :name?', {
                            name: account.name,
                        })
                    "
                    :description="
                        $t(
                            'The user will be able to sign in again. Cancelled registrations stay cancelled.',
                        )
                    "
                    test-id="unblock-user"
                />
                <ActionDialog
                    v-else
                    :action="BlockUserController.form(account.id)"
                    :label="$t('Block')"
                    :title="
                        $t('Block :name?', {
                            name: account.name,
                        })
                    "
                    :description="
                        $t(
                            'The user will not be able to sign in, and their pending registrations will be cancelled.',
                        )
                    "
                    variant="destructive"
                    test-id="block-user"
                />
                <ActionDialog
                    :action="UserController.destroy.form(account.id)"
                    :label="$t('Delete')"
                    :title="
                        $t('Delete the account of :name?', {
                            name: account.name,
                        })
                    "
                    :description="
                        $t(
                            'The user will not be able to sign in, and their pending registrations will be cancelled. The account can be restored.',
                        )
                    "
                    variant="destructive"
                    test-id="delete-user"
                />
            </div>
            <div v-if="account.deleted_at" class="mb-8 sm:ml-auto">
                <ActionDialog
                    :action="RestoreUserController.form(account.id)"
                    :label="$t('Restore')"
                    :title="
                        $t('Restore the account of :name?', {
                            name: account.name,
                        })
                    "
                    :description="
                        $t(
                            'The user will be able to sign in again. Cancelled registrations stay cancelled.',
                        )
                    "
                    test-id="restore-user"
                />
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>{{ $t('Profile') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <ProfileDetails :participant="account" />
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>{{ $t('Account') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <AccountDetails :account="account" />
                </CardContent>
            </Card>
        </div>

        <div class="flex flex-col gap-3">
            <h2 class="text-lg font-semibold">{{ $t('Registrations') }}</h2>

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>{{ $t('Event') }}</TableHead>
                        <TableHead>{{ $t('Attendance') }}</TableHead>
                        <TableHead class="text-right">{{
                            $t('Amount')
                        }}</TableHead>
                        <TableHead>{{ $t('Status') }}</TableHead>
                        <TableHead>{{ $t('Registered at') }}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="registration in registrations.data"
                        :key="registration.id"
                        data-test="user-registration-row"
                    >
                        <TableCell class="max-w-72 whitespace-normal">
                            <Link
                                :href="
                                    RegistrationController.show(registration.id)
                                "
                                class="font-medium hover:underline"
                            >
                                {{ registration.event.title }}
                            </Link>
                            <Badge
                                v-if="registration.event.is_deleted"
                                variant="secondary"
                                class="ml-1"
                            >
                                {{ $t('Event deleted') }}
                            </Badge>
                        </TableCell>
                        <TableCell>{{
                            registration.attendance_label
                        }}</TableCell>
                        <TableCell class="text-right tabular-nums">
                            {{ formatPrice(registration.amount) }}
                        </TableCell>
                        <TableCell>
                            <RegistrationStatusBadge
                                :status="registration.status"
                                :label="registration.status_label"
                            />
                        </TableCell>
                        <TableCell class="text-muted-foreground tabular-nums">
                            {{
                                registration.created_at
                                    ? dateFormatter.format(
                                          new Date(registration.created_at),
                                      )
                                    : ''
                            }}
                        </TableCell>
                    </TableRow>
                    <TableEmpty
                        v-if="registrations.data.length === 0"
                        :colspan="5"
                    >
                        {{ $t('No registrations yet.') }}
                    </TableEmpty>
                </TableBody>
            </Table>

            <PaginationControls :paginator="registrations" />
        </div>
    </div>
</template>
