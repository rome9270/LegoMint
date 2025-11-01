def calculate_intercept(x1, y1, m):
    if m is None:
        return None
    return y1 - m * x1
