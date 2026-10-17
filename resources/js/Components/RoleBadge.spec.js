import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import RoleBadge from './RoleBadge.vue';

describe('RoleBadge', () => {
    it('labels the owner role', () => {
        const wrapper = mount(RoleBadge, { props: { role: 'owner' } });

        expect(wrapper.text()).toBe('Owner');
        expect(wrapper.attributes('data-role')).toBe('owner');
    });

    it('labels a member', () => {
        const wrapper = mount(RoleBadge, { props: { role: 'member' } });

        expect(wrapper.text()).toBe('Member');
        expect(wrapper.attributes('data-role')).toBe('member');
    });
});
