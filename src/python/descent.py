import numpy as np


def default_limit_theta(theta):
    return theta


def gradient_descent(x, y, iterations, predict, derivative, theta=None, limit_theta=None):
    assert x.shape[0] == y.shape[0]
    if theta is None:
        theta = np.zeros(x.shape[1])
    assert theta.shape[0] == x.shape[1]

    if limit_theta is None:
        limit_theta = default_limit_theta

    number_of_samples = y.size

    previous_theta_correction_sign = np.zeros(theta.shape)

    predicted_y = predict(x, theta)
    error_y = predicted_y - y
    error_squared = np.sum(np.square(error_y))
    previous_error_squared = error_squared

    # set the initial rate
    alpha = 0.1 * np.ones(theta.shape)

    for i in range(iterations):
        # How much effect would updating theta have on each value?
        derivatives = derivative(x, theta)

        theta_correction = (1.0 / number_of_samples) * alpha * (derivatives.T.dot(error_y)).reshape(theta.shape)

        next_theta = limit_theta(theta - theta_correction)

        predicted_y = predict(x, next_theta)
        error_y = predicted_y - y
        error_squared = np.sum(np.square(error_y))

        if error_squared > previous_error_squared:
            # If the error is larger than the previous result, skip entirely and reduce the learning rate
            # There must be a better way
            alpha = alpha * 0.5

            # reset
            predicted_y = predict(x, theta)
            error_y = predicted_y - y
        else:
            theta = next_theta
            previous_error_squared = error_squared

            # update alpha: If the sign changed, halve the rate otherwise double the rate

            theta_correction_sign = np.sign(theta_correction)
            sign_equalness = np.equal(theta_correction_sign, previous_theta_correction_sign)
            alpha = alpha * np.power(2, np.array(sign_equalness * 2 - 1, dtype=float))
            previous_theta_correction_sign = theta_correction_sign

    print('Mean squared error: {0: 1.3e}'.format(error_squared / number_of_samples))
    return theta
