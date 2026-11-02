import { usePage } from '@inertiajs/vue3';
import type { ComputedRef } from 'vue';
import { computed } from 'vue';
import type { User } from '@/types';

/**
 * The signed-in user, for pages behind the auth middleware where a guest
 * cannot reach the component.
 */
export function useAuthenticatedUser(): ComputedRef<User> {
    const page = usePage();

    return computed(() => {
        const user = page.props.auth.user;

        if (user === null) {
            throw new Error('Expected an authenticated user.');
        }

        return user;
    });
}
