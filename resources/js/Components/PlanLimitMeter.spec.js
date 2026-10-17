import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import PlanLimitMeter from './PlanLimitMeter.vue';

describe('PlanLimitMeter', () => {
    it('shows a numeric cap', () => {
        const wrapper = mount(PlanLimitMeter, {
            props: { label: 'Projects', used: 2, limit: 3 },
        });

        expect(wrapper.text()).toContain('Projects');
        expect(wrapper.text()).toContain('2 / 3');
        expect(wrapper.attributes('data-state')).toBe('ok');
    });

    it('treats a null limit as unlimited', () => {
        const wrapper = mount(PlanLimitMeter, {
            props: { label: 'Members', used: 4, limit: null },
        });

        expect(wrapper.text()).toContain('4 / Unlimited');
        expect(wrapper.attributes('data-state')).toBe('ok');
    });

    it('marks the meter when the cap is reached', () => {
        const wrapper = mount(PlanLimitMeter, {
            props: { label: 'Projects', used: 3, limit: 3 },
        });

        expect(wrapper.attributes('data-state')).toBe('reached');
    });
});
