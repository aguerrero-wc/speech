import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/react';

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}

export default function Login({
    status,
    canResetPassword,
    canRegister,
}: LoginProps) {
    return (
        <div className="min-h-screen bg-orange-50 flex items-center justify-center p-4">
            <Head title="Log in" />

            <div className="w-full max-w-md">
                {/* Logo o título superior */}
                <div className="text-center mb-8">
                    <h1 className="text-3xl font-bold text-orange-600 mb-2">
                        Log in to your account
                    </h1>
                    <p className="text-gray-600 text-sm">
                        Enter your email and password below to log in
                    </p>
                </div>

                {/* Status message */}
                {status && (
                    <div className="mb-6 text-center bg-green-50 border border-green-200 rounded-2xl p-4">
                        <p className="text-sm font-medium text-green-600">
                            {status}
                        </p>
                    </div>
                )}

                {/* Card del formulario */}
                <div className="bg-white border border-orange-200 rounded-3xl shadow-xl p-8">
                    <Form
                        {...store.form()}
                        resetOnSuccess={['password']}
                        className="flex flex-col gap-6"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-6">
                                    {/* Email field */}
                                    <div className="grid gap-2">
                                        <Label 
                                            htmlFor="email"
                                            className="text-gray-700 font-medium"
                                        >
                                            Email address
                                        </Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            name="email"
                                            required
                                            autoFocus
                                            tabIndex={1}
                                            autoComplete="email"
                                            placeholder="email@example.com"
                                            className="rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500 transition-all duration-300"
                                        />
                                        <InputError message={errors.email} />
                                    </div>

                                    {/* Password field */}
                                    <div className="grid gap-2">
                                        <div className="flex items-center justify-between">
                                            <Label 
                                                htmlFor="password"
                                                className="text-gray-700 font-medium"
                                            >
                                                Password
                                            </Label>
                                            {canResetPassword && (
                                                <TextLink
                                                    href={request()}
                                                    className="text-sm text-orange-600 hover:text-orange-700 transition-colors duration-300 font-medium"
                                                    tabIndex={5}
                                                >
                                                    Forgot password?
                                                </TextLink>
                                            )}
                                        </div>
                                        <Input
                                            id="password"
                                            type="password"
                                            name="password"
                                            required
                                            tabIndex={2}
                                            autoComplete="current-password"
                                            placeholder="Password"
                                            className="rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500 transition-all duration-300"
                                        />
                                        <InputError message={errors.password} />
                                    </div>

                                    {/* Remember me checkbox */}
                                    <div className="flex items-center space-x-3">
                                        <Checkbox
                                            id="remember"
                                            name="remember"
                                            tabIndex={3}
                                            className="border-gray-300 data-[state=checked]:bg-orange-500 data-[state=checked]:border-orange-500 transition-all duration-300"
                                        />
                                        <Label 
                                            htmlFor="remember"
                                            className="text-gray-700 font-medium cursor-pointer"
                                        >
                                            Remember me
                                        </Label>
                                    </div>

                                    {/* Submit button */}
                                    <Button
                                        type="submit"
                                        className="mt-4 w-full bg-orange-500 hover:bg-orange-600 text-white rounded-xl py-6 transition-all duration-300 hover:shadow-lg font-medium text-base shadow-md"
                                        tabIndex={4}
                                        disabled={processing}
                                        data-test="login-button"
                                    >
                                        {processing && <Spinner className="mr-2" />}
                                        Log in
                                    </Button>
                                </div>

                                {/* Register link */}
                                {canRegister && (
                                    <div className="text-center text-sm text-gray-600 pt-4 border-t border-orange-100">
                                        Don't have an account?{' '}
                                        <TextLink 
                                            href={register()} 
                                            tabIndex={5}
                                            className="text-orange-600 hover:text-orange-700 transition-colors duration-300 font-semibold"
                                        >
                                            Sign up
                                        </TextLink>
                                    </div>
                                )}
                            </>
                        )}
                    </Form>
                </div>

                {/* Footer decorativo (opcional) */}
                <div className="mt-8 text-center">
                    <p className="text-gray-500 text-xs">
                        © 2025 Your Company. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    );
}